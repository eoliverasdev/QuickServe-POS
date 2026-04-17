import 'dart:async';
import 'dart:math';

import 'package:flutter/material.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/tpv_theme.dart';
import '../../auth/data/auth_service.dart';
import '../data/parked_tickets_store.dart';
import '../data/tpv_catalog_service.dart';
import '../data/tpv_sales_service.dart';
import '../domain/tpv_models.dart';
import 'payment_page.dart';
import 'pending_preorders_page.dart';

class TpvPage extends StatefulWidget {
  const TpvPage({
    super.key,
    required this.authService,
    required this.userName,
    required this.onLogout,
  });

  final AuthService authService;
  final String userName;
  final Future<void> Function() onLogout;

  @override
  State<TpvPage> createState() => _TpvPageState();
}

class _TpvPageState extends State<TpvPage> {
  static const String _adminPin = '1234';
  static const String _halfChickenName = '1/2 Pollastre (Pit i cuixa)';
  static const String _fullChickenName = 'Pollastre';
  static const String _bagProductName = 'Bossa';
  static const int _bagMaxCount = 50;

  final ParkedTicketsStore _parkedTicketsStore = ParkedTicketsStore();
  final Map<String, CartItem> _cart = <String, CartItem>{};
  final TextEditingController _preorderCustomerController = TextEditingController();
  final TextEditingController _preorderTimeController = TextEditingController();
  final TextEditingController _adminPinController = TextEditingController();

  List<TpvCategory> _categories = <TpvCategory>[TpvCategory(id: 'all', name: 'Tots')];
  List<TpvProduct> _products = <TpvProduct>[];
  List<TpvWorker> _workers = <TpvWorker>[];
  List<TpvPreorder> _pendingPreorders = <TpvPreorder>[];
  List<ParkedTicket> _parkedTickets = <ParkedTicket>[];

  String _selectedCategory = 'all';
  bool _loadingCatalog = true;
  bool _submittingOrder = false;
  bool _loggingOut = false;
  String? _catalogError;
  Timer? _pendingRefreshTimer;

  static const double _dialogMaxWidth = 760;
  static const double _dialogVerticalMargin = 24;

  BoxConstraints _dialogConstraints(BuildContext context, {double maxWidth = _dialogMaxWidth}) {
    final Size size = MediaQuery.of(context).size;
    // Prevent render overflows on smaller heights (tablet/web resize).
    final double maxHeight = max(420, size.height - (_dialogVerticalMargin * 2));
    return BoxConstraints(maxWidth: maxWidth, maxHeight: maxHeight);
  }

  @override
  void initState() {
    super.initState();
    _loadInitialData();
    _pendingRefreshTimer = Timer.periodic(const Duration(seconds: 30), (_) => _loadPendingPreorders());
  }

  @override
  void dispose() {
    _pendingRefreshTimer?.cancel();
    _preorderCustomerController.dispose();
    _preorderTimeController.dispose();
    _adminPinController.dispose();
    super.dispose();
  }

  List<TpvProduct> get _filteredProducts {
    if (_selectedCategory == 'all') return _products;
    return _products.where((TpvProduct p) => p.categoryIds.contains(_selectedCategory)).toList();
  }

  List<CartItem> get _cartItems => _cart.values.toList();
  double get _total => _cartItems.fold(0, (double sum, CartItem item) => sum + item.lineTotal);
  double get _subTotal => _total / 1.21;
  double get _iva => _total - _subTotal;
  int get _pendingUrgentCount => _pendingPreorders.where(_isPreorderUrgent).length;
  TpvProduct? get _bagProduct => _findProductByName(_bagProductName) ?? _findProductByName('Bolsa');
  double get _bagUnitPrice => _bagProduct?.price ?? 0;

  List<CartItem> _buildCheckoutCartItems({
    required List<CartItem> sourceItems,
    required int bagCount,
  }) {
    if (bagCount <= 0 || _bagProduct == null) return sourceItems;
    final CartItem bagItem = CartItem(
      product: _bagProduct!,
      quantity: bagCount,
    );
    return <CartItem>[...sourceItems, bagItem];
  }

  Future<void> _loadInitialData() async {
    await _loadCatalog();
    await _loadWorkers();
    await _loadPendingPreorders();
  }

  Future<void> _loadCatalog() async {
    setState(() {
      _loadingCatalog = true;
      _catalogError = null;
    });

    try {
      final TpvCatalogData data = await TpvCatalogService(ApiClient(), widget.authService).fetchCatalog();
      final List<ParkedTicket> parked = await _parkedTicketsStore.load(data.products);
      if (!mounted) return;
      setState(() {
        _categories = data.categories;
        _products = data.products;
        _parkedTickets = parked;
      });
    } catch (error) {
      if (!mounted) return;
      setState(() => _catalogError = error.toString());
    } finally {
      if (mounted) setState(() => _loadingCatalog = false);
    }
  }

  Future<void> _loadWorkers() async {
    try {
      final List<TpvWorker> workers = await TpvSalesService(ApiClient(), widget.authService).fetchWorkers();
      if (!mounted) return;
      setState(() => _workers = workers);
    } catch (_) {}
  }

  Future<void> _loadPendingPreorders({bool showError = false}) async {
    try {
      final List<TpvPreorder> pending = await TpvSalesService(ApiClient(), widget.authService).fetchPendingPreorders();
      if (!mounted) return;
      setState(() => _pendingPreorders = pending);
    } catch (error) {
      if (!mounted || !showError) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No s\'han pogut carregar els pendents: $error')),
      );
    }
  }

  Future<void> _ensureWorkersLoaded() async {
    if (_workers.isNotEmpty) return;
    await _loadWorkers();
  }

  Future<void> _logout() async {
    setState(() => _loggingOut = true);
    await widget.onLogout();
  }

  Future<void> _handleAddProduct(TpvProduct product) async {
    if (product.name.contains('Pollastre')) {
      final String? notes = await _openChickenOptionsDialog(product);
      if (!mounted || notes == null) return;
      _changeQty(product, 1, notes: notes);
      return;
    }
    _changeQty(product, 1);
  }

  void _changeQty(TpvProduct product, int delta, {String? notes}) {
    final String key = '${product.id}|${notes ?? ''}';
    final CartItem? current = _cart[key];
    final int nextQty = (current?.quantity ?? 0) + delta;

    if (delta > 0 && !_canIncreaseProduct(product)) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Stock insuficient per ${product.name}')),
      );
      return;
    }

    setState(() {
      if (nextQty <= 0) {
        _cart.remove(key);
      } else {
        _cart[key] = CartItem(product: product, quantity: nextQty, notes: notes);
      }
    });
  }

  bool _canIncreaseProduct(TpvProduct product) {
    if (product.stock == null) return true;
    return _usedStockForProduct(product.id) + 1 <= product.stock!;
  }

  double _usedStockForProduct(int productId) {
    final TpvProduct? product = _findProductById(productId);
    if (product == null) return 0;
    final TpvProduct? fullChicken = _findProductByName(_fullChickenName);

    double used = 0;
    for (final CartItem item in _cartItems) {
      if (item.product.id == productId) used += item.quantity;
      if (fullChicken != null && fullChicken.id == productId && item.product.name == _halfChickenName) {
        used += item.quantity * 0.5;
      }
    }
    return used;
  }

  int _qtyForProduct(TpvProduct product) {
    return _cartItems
        .where((CartItem item) => item.product.id == product.id)
        .fold(0, (int sum, CartItem item) => sum + item.quantity);
  }

  int? _remainingStock(TpvProduct product) {
    if (product.stock == null) return null;
    return max(0, (product.stock! - _usedStockForProduct(product.id)).floor());
  }

  void _clearCart() => setState(_cart.clear);

  Future<void> _parkCurrentTicket() async {
    if (_cart.isEmpty) return;
    final ParkedTicket ticket = ParkedTicket(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      createdAt: DateTime.now(),
      items: _cartItems,
    );
    final List<ParkedTicket> next = <ParkedTicket>[ticket, ..._parkedTickets];
    await _parkedTicketsStore.save(next);
    if (!mounted) return;
    setState(() {
      _parkedTickets = next;
      _cart.clear();
    });
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Ticket aparcat')));
  }

  Future<void> _restoreParkedTicket(ParkedTicket ticket) async {
    setState(() {
      _cart
        ..clear()
        ..addEntries(ticket.items.map((CartItem item) => MapEntry<String, CartItem>(item.cartKey, item)));
    });
    final List<ParkedTicket> next = _parkedTickets.where((ParkedTicket t) => t.id != ticket.id).toList();
    await _parkedTicketsStore.save(next);
    if (!mounted) return;
    setState(() => _parkedTickets = next);
  }

  Future<void> _deleteParkedTicket(ParkedTicket ticket) async {
    final List<ParkedTicket> next = _parkedTickets.where((ParkedTicket t) => t.id != ticket.id).toList();
    await _parkedTicketsStore.save(next);
    if (!mounted) return;
    setState(() => _parkedTickets = next);
  }

  Future<void> _openParkedTicketsDialog() async {
    showDialog<void>(
      context: context,
      builder: (BuildContext context) {
        int? expandedTicketIndex;
        return StatefulBuilder(
          builder: (BuildContext context, void Function(void Function()) setModalState) {
            return Dialog(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 760, maxHeight: 600),
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      const Text('Tickets aparcats', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 22)),
                      const SizedBox(height: 8),
                      Expanded(
                        child: _parkedTickets.isEmpty
                            ? const Center(child: Text('No hi ha tickets aparcats'))
                            : ListView.separated(
                                itemCount: _parkedTickets.length,
                                separatorBuilder: (BuildContext context, int index) => const SizedBox(height: 10),
                                itemBuilder: (_, int index) {
                                  final ParkedTicket ticket = _parkedTickets[index];
                                  final double total = ticket.items.fold(0, (double a, CartItem b) => a + b.lineTotal);
                                  final bool expanded = expandedTicketIndex == index;
                                  return Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(12),
                                      color: const Color(0xFFF8F9FE),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: <Widget>[
                                        InkWell(
                                          onTap: () {
                                            setModalState(() {
                                              expandedTicketIndex = expanded ? null : index;
                                            });
                                          },
                                          borderRadius: BorderRadius.circular(10),
                                          child: Padding(
                                            padding: const EdgeInsets.symmetric(vertical: 2),
                                            child: Row(
                                              children: <Widget>[
                                                Expanded(
                                                  child: Text(
                                                    '${ticket.items.length} línies · ${total.toStringAsFixed(2)}€ · ${ticket.createdAt.hour.toString().padLeft(2, '0')}:${ticket.createdAt.minute.toString().padLeft(2, '0')}',
                                                    style: const TextStyle(fontWeight: FontWeight.w700),
                                                  ),
                                                ),
                                                Icon(
                                                  expanded ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                                                  color: TpvTheme.textSecondary,
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                        if (expanded) ...<Widget>[
                                          const SizedBox(height: 8),
                                          Container(
                                            width: double.infinity,
                                            padding: const EdgeInsets.all(10),
                                            decoration: BoxDecoration(
                                              color: Colors.white,
                                              borderRadius: BorderRadius.circular(10),
                                              border: Border.all(color: const Color(0xFFE6EAF5)),
                                            ),
                                            child: Column(
                                              children: ticket.items.map((CartItem item) {
                                                return Padding(
                                                  padding: const EdgeInsets.symmetric(vertical: 3),
                                                  child: Row(
                                                    crossAxisAlignment: CrossAxisAlignment.start,
                                                    children: <Widget>[
                                                      Expanded(
                                                        child: Text(
                                                          '${item.quantity}x ${item.product.name}${(item.notes ?? '').trim().isNotEmpty ? ' · ${item.notes}' : ''}',
                                                          style: const TextStyle(fontSize: 13),
                                                        ),
                                                      ),
                                                      Text(
                                                        '${item.lineTotal.toStringAsFixed(2)}€',
                                                        style: const TextStyle(fontWeight: FontWeight.w700),
                                                      ),
                                                    ],
                                                  ),
                                                );
                                              }).toList(),
                                            ),
                                          ),
                                        ],
                                        const SizedBox(height: 8),
                                        Row(
                                          children: <Widget>[
                                            const Spacer(),
                                            FilledButton(
                                              onPressed: () async {
                                                await _restoreParkedTicket(ticket);
                                                if (!mounted) return;
                                                setModalState(() {});
                                              },
                                              child: const Text('Recuperar'),
                                            ),
                                            const SizedBox(width: 8),
                                            OutlinedButton(
                                              onPressed: () async {
                                                await _deleteParkedTicket(ticket);
                                                if (!mounted) return;
                                                setModalState(() {});
                                              },
                                              style: OutlinedButton.styleFrom(foregroundColor: TpvTheme.danger),
                                              child: const Text('Eliminar'),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  );
                                },
                              ),
                      ),
                      Align(
                        alignment: Alignment.centerRight,
                        child: TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Tancar')),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _openCheckoutDialog() async {
    if (_cart.isEmpty || _submittingOrder) return;
    await _ensureWorkersLoaded();
    if (_workers.isEmpty || !mounted) return;

    final int? workerId = await _openWorkerSelectionDialog(
      title: 'Qui està gestionant?',
      confirmLabel: 'OBRIR COBRAMENT',
    );
    if (!mounted || workerId == null) return;
    await _openPaymentPage(workerId);
  }

  Future<void> _openPaymentPage(int workerId) async {
    final TpvWorker worker = _workers.firstWhere((TpvWorker w) => w.id == workerId);
    await Navigator.of(context).push<void>(
      MaterialPageRoute<void>(
        builder: (_) => PaymentPage(
          workerName: worker.name,
          cartItems: _cartItems,
          initialTotal: _total,
          bagUnitPrice: _bagUnitPrice,
          bagMaxCount: _bagMaxCount,
          onConfirm: ({
            required String paymentMethod,
            required int bagCount,
            required bool discount,
            required double finalTotal,
            double? cashGiven,
          }) async {
            final List<CartItem> checkoutItems = _buildCheckoutCartItems(
              sourceItems: _cartItems,
              bagCount: bagCount,
            );
            return _submitOrder(
              workerId: workerId,
              paymentMethod: paymentMethod,
              totalPrice: finalTotal,
              cartItems: checkoutItems,
              cashGiven: cashGiven,
            );
          },
        ),
      ),
    );
  }

  Future<bool> _submitOrder({
    required int workerId,
    required String paymentMethod,
    required double totalPrice,
    required List<CartItem> cartItems,
    double? cashGiven,
  }) async {
    setState(() => _submittingOrder = true);
    try {
      final Map<String, dynamic> data = await TpvSalesService(ApiClient(), widget.authService).createOrder(
        workerId: workerId,
        paymentMethod: paymentMethod,
        cartItems: cartItems,
        totalPrice: totalPrice,
      );
      if (!mounted) return false;
      final ScaffoldMessengerState messenger = ScaffoldMessenger.of(context);
      await _printOrderTicket(
        orderId: (data['order_id'] as num?)?.toInt(),
        title: 'Ticket venda',
        cashGiven: cashGiven,
      );
      setState(() => _cart.clear());
      messenger.showSnackBar(const SnackBar(content: Text('Venda realitzada')));
      await _loadCatalog();
      return true;
    } catch (error) {
      if (!mounted) return false;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error en la venda: $error')));
      return false;
    } finally {
      if (mounted) setState(() => _submittingOrder = false);
    }
  }

  Future<void> _openPreorderDialog() async {
    if (_cart.isEmpty || _submittingOrder) return;
    await _ensureWorkersLoaded();
    if (_workers.isEmpty || !mounted) return;
    if (_preorderTimeController.text.trim().isEmpty) {
      _preorderTimeController.text = _formatTime(_roundUpToQuarter(DateTime.now()));
    }
    int? workerId = _workers.first.id;

    await showDialog<void>(
      context: context,
      builder: (BuildContext dialogContext) {
        return StatefulBuilder(
          builder: (BuildContext context, void Function(void Function()) setModalState) {
            return Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(26)),
              child: ConstrainedBox(
                constraints: _dialogConstraints(context, maxWidth: 760),
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: <Widget>[
                        const Center(
                          child: Text(
                            'Qui està gestionant?',
                            style: TextStyle(fontSize: 30, fontWeight: FontWeight.w900),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          children: _workers.map((TpvWorker worker) {
                            final bool active = workerId == worker.id;
                            return _workerTouchPill(
                              label: worker.name,
                              active: active,
                              onTap: () => setModalState(() => workerId = worker.id),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 16),
                        const Text('Hora de recollida:', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: <Widget>[
                            _timeAdjustButton('-60m', () => setModalState(() => _adjustPreorderTime(minutesDelta: -60))),
                            _timeAdjustButton('-30m', () => setModalState(() => _adjustPreorderTime(minutesDelta: -30))),
                            _timeAdjustButton('-15m', () => setModalState(() => _adjustPreorderTime(minutesDelta: -15))),
                            _timeAdjustButton('Ara', () => setModalState(() => _adjustPreorderTime(now: true))),
                            _timeAdjustButton('+15m', () => setModalState(() => _adjustPreorderTime(minutesDelta: 15))),
                            _timeAdjustButton('+30m', () => setModalState(() => _adjustPreorderTime(minutesDelta: 30))),
                            _timeAdjustButton('+60m', () => setModalState(() => _adjustPreorderTime(minutesDelta: 60))),
                          ],
                        ),
                        const SizedBox(height: 10),
                        TextField(
                          controller: _preorderTimeController,
                          style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w800),
                          decoration: const InputDecoration(),
                        ),
                        const SizedBox(height: 12),
                        const Text('Nom del Client (Opcional):', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
                        const SizedBox(height: 8),
                        TextField(
                          controller: _preorderCustomerController,
                          style: const TextStyle(fontSize: 18),
                        ),
                        const SizedBox(height: 14),
                        SizedBox(
                          width: double.infinity,
                          child: FilledButton(
                            onPressed: workerId == null || _submittingOrder
                                ? null
                                : () async {
                                    final NavigatorState navigator = Navigator.of(dialogContext);
                                    await _submitPreorder(workerId!);
                                    if (mounted) navigator.pop();
                                  },
                            style: FilledButton.styleFrom(
                              minimumSize: const Size.fromHeight(56),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
                            ),
                            child: const Text('Guardar Encàrrec', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Center(
                          child: TextButton(
                            onPressed: _submittingOrder ? null : () => Navigator.of(dialogContext).pop(),
                            child: const Text('Cancel·lar', style: TextStyle(fontSize: 16, color: TpvTheme.textSecondary)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _submitPreorder(int workerId) async {
    setState(() => _submittingOrder = true);
    try {
      await TpvSalesService(ApiClient(), widget.authService).createOrder(
        workerId: workerId,
        paymentMethod: 'Pendent',
        cartItems: _cartItems,
        totalPrice: _total,
        isPreorder: true,
        pickupTime: _preorderTimeController.text.trim().isEmpty ? null : _preorderTimeController.text.trim(),
        customerName: _preorderCustomerController.text.trim().isEmpty ? null : _preorderCustomerController.text.trim(),
      );
      if (!mounted) return;
      setState(() => _cart.clear());
      _preorderTimeController.clear();
      _preorderCustomerController.clear();
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Encàrrec guardat')));
      await _loadPendingPreorders();
      await _loadCatalog();
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error guardant encàrrec: $error')));
    } finally {
      if (mounted) setState(() => _submittingOrder = false);
    }
  }

  Future<int?> _openWorkerSelectionDialog({
    required String title,
    required String confirmLabel,
  }) async {
    int? selectedWorkerId;
    await showDialog<void>(
      context: context,
      builder: (BuildContext dialogContext) {
        return StatefulBuilder(
          builder: (BuildContext context, void Function(void Function()) setModalState) {
            return Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
              child: ConstrainedBox(
                constraints: _dialogConstraints(context, maxWidth: 760),
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: <Widget>[
                        Text(title, style: const TextStyle(fontSize: 30, fontWeight: FontWeight.w900), textAlign: TextAlign.center),
                        const SizedBox(height: 14),
                        Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          children: _workers.map((TpvWorker worker) {
                            final bool active = selectedWorkerId == worker.id;
                            return _workerTouchPill(
                              label: worker.name,
                              active: active,
                              onTap: () => setModalState(() => selectedWorkerId = worker.id),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 16),
                        SizedBox(
                          width: double.infinity,
                          child: FilledButton(
                            onPressed: selectedWorkerId == null ? null : () => Navigator.of(dialogContext).pop(),
                            style: FilledButton.styleFrom(
                              minimumSize: const Size.fromHeight(56),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
                            ),
                            child: Text(confirmLabel, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                          ),
                        ),
                        Center(
                          child: TextButton(
                            onPressed: () {
                              selectedWorkerId = null;
                              Navigator.of(dialogContext).pop();
                            },
                            child: const Text('Cancel·lar', style: TextStyle(fontSize: 16, color: TpvTheme.textSecondary)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        );
      },
    );
    return selectedWorkerId;
  }

  Future<void> _openPendingPreordersPage() async {
    await Navigator.of(context).push<void>(
      MaterialPageRoute<void>(
        builder: (_) => PendingPreordersPage(
          authService: widget.authService,
          onBack: () => Navigator.of(context).pop(),
          onOpenProductsSummary: _openPendingProductsSummaryDialog,
          onCharge: (TpvPreorder preorder) => _openChargePreorderPaymentPage(preorder),
          onModify: (TpvPreorder preorder) async {
            final bool ok = await _editPreorder(preorder);
            if (!ok || !mounted) return false;
            Navigator.of(context).pop();
            return true;
          },
          onCancel: (TpvPreorder preorder) => _cancelPreorder(preorder),
        ),
      ),
    );
  }

  bool _isPreorderUrgent(TpvPreorder preorder) {
    final String? time = preorder.pickupTime;
    if (time == null || !time.contains(':')) return false;
    final List<String> parts = time.split(':');
    final int? h = int.tryParse(parts.first);
    final int? m = int.tryParse(parts.last);
    if (h == null || m == null) return false;
    final DateTime now = DateTime.now();
    final int diff = (h * 60 + m) - (now.hour * 60 + now.minute);
    return diff <= 15 && diff >= -120;
  }

  Future<void> _openPendingProductsSummaryDialog() async {
    final Map<String, int> totals = <String, int>{};
    for (final TpvPreorder preorder in _pendingPreorders) {
      try {
        final TpvOrderDetail detail = await TpvSalesService(ApiClient(), widget.authService).fetchOrderDetails(orderId: preorder.id);
        for (final TpvOrderDetailItem item in detail.items) {
          totals[item.productName] = (totals[item.productName] ?? 0) + item.quantity;
        }
      } catch (_) {}
    }
    if (!mounted) return;

    final List<MapEntry<String, int>> sorted = totals.entries.toList()..sort((a, b) => b.value.compareTo(a.value));
    showDialog<void>(
      context: context,
      builder: (_) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 700, maxHeight: 760),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(22, 18, 22, 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Row(
                    children: <Widget>[
                      const SizedBox(width: 8),
                      const Expanded(
                        child: Text(
                          'Sumatori de Productes',
                          style: TextStyle(fontSize: 38, fontWeight: FontWeight.w900),
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.of(context).pop(),
                        icon: const Icon(Icons.close, color: TpvTheme.textSecondary),
                      ),
                    ],
                  ),
                  Text(
                    sorted.isEmpty ? 'Sense productes pendents' : '${sorted.length} productes amb encàrrecs pendents',
                    style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 12),
                  Expanded(
                    child: sorted.isEmpty
                        ? const Center(
                            child: Text(
                              'No hi ha productes pendents',
                              style: TextStyle(fontSize: 16, color: TpvTheme.textSecondary),
                            ),
                          )
                        : ListView.separated(
                            itemCount: sorted.length,
                            separatorBuilder: (_, _) => const SizedBox(height: 8),
                            itemBuilder: (_, int index) {
                              final MapEntry<String, int> entry = sorted[index];
                              return Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF7F8FD),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFE6EAF5)),
                                ),
                                child: Row(
                                  children: <Widget>[
                                    Expanded(
                                      child: Text(
                                        entry.key,
                                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                      decoration: BoxDecoration(
                                        color: TpvTheme.primary,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        '${entry.value}',
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.w900,
                                          fontSize: 20,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                  ),
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () => Navigator.of(context).pop(),
                      child: const Text('Tancar', style: TextStyle(fontSize: 20)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Future<bool> _openChargePreorderPaymentPage(TpvPreorder order) async {
    await _ensureWorkersLoaded();
    if (_workers.isEmpty || !mounted) return false;

    final int? workerId = await _openWorkerSelectionDialog(
      title: 'Qui està gestionant?',
      confirmLabel: 'OBRIR COBRAMENT',
    );
    if (!mounted || workerId == null) return false;

    final TpvWorker worker = _workers.firstWhere((TpvWorker w) => w.id == workerId);
    final TpvSalesService salesService = TpvSalesService(ApiClient(), widget.authService);
    TpvOrderDetail? detail;
    try {
      detail = await salesService.fetchOrderDetails(orderId: order.id);
    } catch (_) {}

    final List<CartItem> detailItems = detail?.items.map((TpvOrderDetailItem item) {
          return CartItem(
            product: TpvProduct(
              id: item.productId,
              name: item.productName,
              price: item.priceAtSale,
              categoryIds: const <String>[],
            ),
            quantity: item.quantity,
            notes: item.notes,
          );
        }).toList() ??
        <CartItem>[
          CartItem(
            product: TpvProduct(
              id: -order.id,
              name: 'Encàrrec #${order.pickupNumber ?? order.id}',
              price: order.totalPrice,
              categoryIds: const <String>[],
            ),
            quantity: 1,
          ),
        ];

    bool didCharge = false;
    if (!mounted) return false;

    await Navigator.of(context).push<void>(
      MaterialPageRoute<void>(
        builder: (_) => PaymentPage(
          title: 'Cobrar encàrrec #${order.pickupNumber ?? order.id}',
          subtitle: '${order.customerName ?? 'Sense nom'} · ${order.pickupTime ?? '--:--'} · ${worker.name}',
          confirmLabel: '✅ Confirmar cobrament',
          showDiscount: false,
          workerName: worker.name,
          cartItems: detailItems,
          initialTotal: order.totalPrice,
          bagUnitPrice: _bagUnitPrice,
          bagMaxCount: _bagMaxCount,
          onConfirm: ({
            required String paymentMethod,
            required int bagCount,
            required bool discount,
            required double finalTotal,
            double? cashGiven,
          }) async {
            final ScaffoldMessengerState messenger = ScaffoldMessenger.of(context);
            setState(() => _submittingOrder = true);
            try {
              await salesService.chargePreorder(
                orderId: order.id,
                paymentMethod: paymentMethod,
                workerId: workerId,
                bagCount: bagCount,
                bagProductId: _bagProduct?.id,
              );
              if (!mounted) return false;
              didCharge = true;
              await _printOrderTicket(
                orderId: order.id,
                title: 'Ticket encàrrec cobrat',
                cashGiven: cashGiven,
              );
              messenger.showSnackBar(const SnackBar(content: Text('Encàrrec cobrat')));
              return true;
            } catch (error) {
              if (!mounted) return false;
              messenger.showSnackBar(SnackBar(content: Text('Error cobrant: $error')));
              return false;
            } finally {
              if (mounted) setState(() => _submittingOrder = false);
            }
          },
        ),
      ),
    );
    return didCharge;
  }

  Future<bool> _cancelPreorder(TpvPreorder preorder) async {
    final bool? confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Anul·lar encàrrec'),
        content: Text('Segur que vols anul·lar #${preorder.pickupNumber ?? preorder.id}?'),
        actions: <Widget>[
          TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('No')),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: FilledButton.styleFrom(backgroundColor: TpvTheme.danger),
            child: const Text('Sí'),
          ),
        ],
      ),
    );

    if (confirm != true) return false;
    setState(() => _submittingOrder = true);
    try {
      await TpvSalesService(ApiClient(), widget.authService).cancelPreorder(orderId: preorder.id);
      if (!mounted) return false;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Encàrrec anul·lat')));
      await _loadCatalog();
      return true;
    } catch (error) {
      if (!mounted) return false;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error anul·lant: $error')));
      return false;
    } finally {
      if (mounted) setState(() => _submittingOrder = false);
    }
  }

  Future<bool> _editPreorder(TpvPreorder preorder) async {
    final ScaffoldMessengerState messenger = ScaffoldMessenger.of(context);
    setState(() => _submittingOrder = true);
    try {
      final TpvOrderDetail detail = await TpvSalesService(ApiClient(), widget.authService).fetchOrderDetails(orderId: preorder.id);
      await TpvSalesService(ApiClient(), widget.authService).cancelPreorder(orderId: preorder.id);
      if (!mounted) return false;

      final Map<String, CartItem> updated = <String, CartItem>{};
      for (final TpvOrderDetailItem item in detail.items) {
        final TpvProduct? product = _findProductById(item.productId);
        if (product == null) continue;
        final CartItem cartItem = CartItem(product: product, quantity: item.quantity, notes: item.notes);
        updated[cartItem.cartKey] = cartItem;
      }

      setState(() {
        _cart
          ..clear()
          ..addAll(updated);
      });
      _preorderCustomerController.text = detail.customerName ?? '';
      _preorderTimeController.text = _normalizePickupTime(detail.pickupTime);
      await _loadPendingPreorders();
      await _loadCatalog();
      messenger.showSnackBar(const SnackBar(content: Text('Encàrrec carregat per editar')));
      return true;
    } catch (error) {
      if (!mounted) return false;
      messenger.showSnackBar(SnackBar(content: Text('Error editant encàrrec: $error')));
      return false;
    } finally {
      if (mounted) setState(() => _submittingOrder = false);
    }
  }

  Future<void> _openAdminPinDialog() async {
    _adminPinController.clear();
    await showDialog<void>(
      context: context,
      builder: (_) {
        final FocusNode pinFocusNode = FocusNode();
        const int pinLength = 4;

        Widget keypadButton({
          required String label,
          required VoidCallback onTap,
          bool primary = false,
        }) {
          return SizedBox(
            height: 72,
            child: ElevatedButton(
              onPressed: onTap,
              style: ElevatedButton.styleFrom(
                elevation: 0,
                backgroundColor: primary ? TpvTheme.primary : const Color(0xFFF6F7FC),
                foregroundColor: primary ? Colors.white : TpvTheme.textMain,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                  side: BorderSide(color: primary ? TpvTheme.primary : const Color(0xFFE3E6F1)),
                ),
              ),
              child: Text(label, style: const TextStyle(fontSize: 34, fontWeight: FontWeight.w800)),
            ),
          );
        }

        return StatefulBuilder(
          builder: (BuildContext context, void Function(void Function()) setModalState) {
            final String currentPin = _adminPinController.text;
            final bool canSubmit = currentPin.length == pinLength;

            void appendDigit(String digit) {
              if (_adminPinController.text.length >= pinLength) return;
              _adminPinController.text = '${_adminPinController.text}$digit';
              _adminPinController.selection = TextSelection.collapsed(offset: _adminPinController.text.length);
              setModalState(() {});
            }

            void removeLastDigit() {
              if (_adminPinController.text.isEmpty) return;
              _adminPinController.text = _adminPinController.text.substring(0, _adminPinController.text.length - 1);
              _adminPinController.selection = TextSelection.collapsed(offset: _adminPinController.text.length);
              setModalState(() {});
            }

            void clearPin() {
              _adminPinController.clear();
              setModalState(() {});
            }

            void submit() {
              if (!canSubmit) return;
              final bool ok = _adminPinController.text.trim() == _adminPin;
              Navigator.of(context).pop();
              ScaffoldMessenger.of(this.context).showSnackBar(
                SnackBar(content: Text(ok ? 'PIN correcte' : 'PIN incorrecte')),
              );
            }

            return Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 680),
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(24, 18, 24, 14),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: <Widget>[
                      const Text('Accés Administració', style: TextStyle(fontSize: 46, fontWeight: FontWeight.w900)),
                      const SizedBox(height: 8),
                      const Text(
                        'Introdueix el PIN d\'encarregat.',
                        style: TextStyle(fontSize: 16, color: TpvTheme.textSecondary, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List<Widget>.generate(pinLength, (int index) {
                          final bool filled = currentPin.length > index;
                          return Container(
                            margin: const EdgeInsets.symmetric(horizontal: 6),
                            width: 16,
                            height: 16,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: filled ? TpvTheme.primary : Colors.white,
                              border: Border.all(color: const Color(0xFFCFD4E3), width: 2),
                            ),
                          );
                        }),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _adminPinController,
                        focusNode: pinFocusNode,
                        keyboardType: TextInputType.number,
                        obscureText: true,
                        obscuringCharacter: '*',
                        maxLength: pinLength,
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontSize: 34, fontWeight: FontWeight.w800, letterSpacing: 10),
                        decoration: const InputDecoration(
                          counterText: '',
                          hintText: '••••',
                        ),
                        onChanged: (String value) {
                          final String digitsOnly = value.replaceAll(RegExp(r'[^0-9]'), '');
                          if (digitsOnly != value || digitsOnly.length > pinLength) {
                            _adminPinController.text = digitsOnly.substring(0, min(pinLength, digitsOnly.length));
                            _adminPinController.selection =
                                TextSelection.collapsed(offset: _adminPinController.text.length);
                          }
                          setModalState(() {});
                        },
                      ),
                      const SizedBox(height: 10),
                      for (final List<String> row in <List<String>>[
                        <String>['1', '2', '3'],
                        <String>['4', '5', '6'],
                        <String>['7', '8', '9'],
                      ]) ...<Widget>[
                        Row(
                          children: row
                              .map((String digit) => Expanded(
                                    child: Padding(
                                      padding: const EdgeInsets.all(6),
                                      child: keypadButton(label: digit, onTap: () => appendDigit(digit)),
                                    ),
                                  ))
                              .toList(),
                        ),
                      ],
                      Row(
                        children: <Widget>[
                          Expanded(
                            child: Padding(
                              padding: const EdgeInsets.all(6),
                              child: keypadButton(label: 'Netejar', onTap: clearPin),
                            ),
                          ),
                          Expanded(
                            child: Padding(
                              padding: const EdgeInsets.all(6),
                              child: keypadButton(label: '0', onTap: () => appendDigit('0')),
                            ),
                          ),
                          Expanded(
                            child: Padding(
                              padding: const EdgeInsets.all(6),
                              child: keypadButton(label: '⌫', onTap: removeLastDigit),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton(
                          onPressed: canSubmit ? submit : null,
                          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(58)),
                          child: const Text('Accedir', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900)),
                        ),
                      ),
                      const SizedBox(height: 4),
                      TextButton(
                        onPressed: () => Navigator.of(context).pop(),
                        child: const Text('Cancel·lar', style: TextStyle(fontSize: 20)),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<String?> _openChickenOptionsDialog(TpvProduct product) async {
    final bool isWholeChicken = product.name == _fullChickenName;
    String selectedPart = 'Pit i cuixa';
    String selectedCook = 'Normal';
    bool withSauce = false;
    final TextEditingController notesController = TextEditingController();
    String? result;

    await showDialog<void>(
      context: context,
      builder: (_) {
        return StatefulBuilder(
          builder: (BuildContext context, void Function(void Function()) setModalState) {
            final List<String> sauceOptions = <String>['Amb suc', 'Sense suc'];
            final List<String> cookOptions = <String>['Normal', 'Poc cuit', 'Molt cuit'];
            final List<String> partOptions = <String>['Pit i cuixa', 'Només pit', 'Només cuixa'];

            return Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 620),
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Text(
                        'Opcions: ${product.name}',
                        style: const TextStyle(fontSize: 30, fontWeight: FontWeight.w900),
                      ),
                      const SizedBox(height: 12),
                      if (!isWholeChicken) ...<Widget>[
                        const Text(
                          'PART',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: TpvTheme.textSecondary),
                        ),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: partOptions.map((String option) {
                            return _chickenOptionPill(
                              label: option,
                              selected: selectedPart == option,
                              onTap: () => setModalState(() => selectedPart = option),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 12),
                      ],
                      const Text(
                        'OPCIONS DE SUC',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: TpvTheme.textSecondary),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: sauceOptions.map((String option) {
                          final bool selected = (option == 'Amb suc' && withSauce) || (option == 'Sense suc' && !withSauce);
                          return Expanded(
                            child: Padding(
                              padding: const EdgeInsets.only(right: 8),
                              child: _chickenOptionPill(
                                label: option,
                                selected: selected,
                                onTap: () => setModalState(() => withSauce = option == 'Amb suc'),
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'PUNT DE COCCIÓ',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: TpvTheme.textSecondary),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: cookOptions.map((String option) {
                          return Expanded(
                            child: Padding(
                              padding: const EdgeInsets.only(right: 8),
                              child: _chickenOptionPill(
                                label: option,
                                selected: selectedCook == option,
                                onTap: () => setModalState(() => selectedCook = option),
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: notesController,
                        style: const TextStyle(fontSize: 16),
                        decoration: const InputDecoration(hintText: 'Nota extra (opcional)'),
                      ),
                      const SizedBox(height: 10),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton(
                          onPressed: () {
                            final String custom = notesController.text.trim();
                            final String sauceText = withSauce ? 'Amb suc' : 'Sense suc';
                            final List<String> parts = <String>[
                              if (!isWholeChicken) selectedPart,
                              selectedCook,
                              sauceText,
                              if (custom.isNotEmpty) custom,
                            ];
                            result = parts.join(' · ');
                            Navigator.of(context).pop();
                          },
                          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(48)),
                          child: const Text('Afegir a la comanda', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                        ),
                      ),
                      Center(
                        child: TextButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text('Cancel·lar', style: TextStyle(fontSize: 16)),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );

    notesController.dispose();
    return result;
  }

  Widget _chickenOptionPill({
    required String label,
    required bool selected,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(22),
      child: Container(
        height: 54,
        constraints: const BoxConstraints(minWidth: 140),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          color: selected ? const Color(0xFFEFEDD2) : const Color(0xFFF6F6F6),
          border: Border.all(
            color: selected ? TpvTheme.primary : const Color(0xFFDADADA),
            width: selected ? 2 : 1.3,
          ),
        ),
        alignment: Alignment.center,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        child: Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: selected ? Colors.black : const Color(0xFF6D6D6D),
          ),
        ),
      ),
    );
  }

  Future<void> _printOrderTicket({
    required int? orderId,
    required String title,
    double? cashGiven,
  }) async {
    if (orderId == null) return;
    try {
      final TpvOrderDetail detail = await TpvSalesService(ApiClient(), widget.authService).fetchOrderDetails(orderId: orderId);
      await Printing.layoutPdf(
        onLayout: (PdfPageFormat format) async {
          final pw.Document doc = pw.Document();
          final DateTime ticketDate = detail.createdAt ?? DateTime.now();
          final String dateLabel =
              '${ticketDate.day.toString().padLeft(2, '0')}/${ticketDate.month.toString().padLeft(2, '0')}/${ticketDate.year}';
          final String invoiceLabel =
              (detail.fiscalFullNumber != null && detail.fiscalFullNumber!.trim().isNotEmpty)
                  ? detail.fiscalFullNumber!
                  : orderId.toString().padLeft(8, '0');
          final bool isCash = detail.paymentMethod.toLowerCase().contains('efectiu');
          final double delivered = cashGiven ?? 0;
          final double change = isCash && delivered > 0 ? max(0, delivered - detail.totalPrice) : 0;
          final double base10 = detail.totalPrice / 1.10;
          final double iva10 = detail.totalPrice - base10;

          doc.addPage(
            pw.Page(
              pageFormat: format.copyWith(width: 226, marginLeft: 10, marginRight: 10, marginTop: 10, marginBottom: 10),
              build: (_) {
                return pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: <pw.Widget>[
                    pw.Center(child: pw.Text('LA CRESTA', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 13))),
                    pw.Center(child: pw.Text('C/ Sant Andreu, 6', style: const pw.TextStyle(fontSize: 9))),
                    pw.Center(child: pw.Text('17846 - Mata', style: const pw.TextStyle(fontSize: 9))),
                    pw.Center(child: pw.Text('Tel. 972 57 34 03', style: const pw.TextStyle(fontSize: 9))),
                    pw.Center(child: pw.Text('NIF: B17880782', style: const pw.TextStyle(fontSize: 9))),
                    pw.SizedBox(height: 5),
                    pw.Row(
                      mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                      children: <pw.Widget>[
                        pw.Text('FACTURA: $invoiceLabel', style: const pw.TextStyle(fontSize: 8)),
                        pw.Text('DATA: $dateLabel', style: const pw.TextStyle(fontSize: 8)),
                      ],
                    ),
                    if (title.isNotEmpty) ...<pw.Widget>[
                      pw.SizedBox(height: 2),
                      pw.Text(title.toUpperCase(), style: const pw.TextStyle(fontSize: 8)),
                    ],
                    if (detail.customerName != null && detail.customerName!.trim().isNotEmpty)
                      pw.Text('CLIENT: ${detail.customerName}', style: const pw.TextStyle(fontSize: 8)),
                    pw.Divider(),
                    pw.Row(
                      children: <pw.Widget>[
                        pw.Expanded(flex: 2, child: pw.Text('UNIT.', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8))),
                        pw.Expanded(flex: 6, child: pw.Text('DESCRIPCIÓ', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8))),
                        pw.Expanded(
                          flex: 2,
                          child: pw.Align(
                            alignment: pw.Alignment.centerRight,
                            child: pw.Text('PREU', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8)),
                          ),
                        ),
                        pw.Expanded(
                          flex: 2,
                          child: pw.Align(
                            alignment: pw.Alignment.centerRight,
                            child: pw.Text('IMPORT', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8)),
                          ),
                        ),
                      ],
                    ),
                    pw.SizedBox(height: 4),
                    ...detail.items.map((TpvOrderDetailItem item) {
                      final String unitLabel = '${item.quantity.toStringAsFixed(0)},000';
                      final String price = item.priceAtSale.toStringAsFixed(2);
                      final String lineImport = (item.priceAtSale * item.quantity).toStringAsFixed(2);
                      return pw.Column(
                        crossAxisAlignment: pw.CrossAxisAlignment.start,
                        children: <pw.Widget>[
                          pw.Row(
                            children: <pw.Widget>[
                              pw.Expanded(flex: 2, child: pw.Text(unitLabel, style: const pw.TextStyle(fontSize: 8))),
                              pw.Expanded(flex: 6, child: pw.Text(item.productName.toUpperCase(), style: const pw.TextStyle(fontSize: 8))),
                              pw.Expanded(
                                flex: 2,
                                child: pw.Align(
                                  alignment: pw.Alignment.centerRight,
                                  child: pw.Text(price, style: const pw.TextStyle(fontSize: 8)),
                                ),
                              ),
                              pw.Expanded(
                                flex: 2,
                                child: pw.Align(
                                  alignment: pw.Alignment.centerRight,
                                  child: pw.Text(lineImport, style: const pw.TextStyle(fontSize: 8)),
                                ),
                              ),
                            ],
                          ),
                          if (item.notes != null && item.notes!.isNotEmpty)
                            pw.Padding(
                              padding: const pw.EdgeInsets.only(left: 18),
                              child: pw.Text(item.notes!, style: const pw.TextStyle(fontSize: 7)),
                            ),
                        ],
                      );
                    }),
                    pw.Divider(),
                    _ticketTwoCol('TOTAL', detail.totalPrice.toStringAsFixed(2), bold: true, fontSize: 14),
                    if (isCash && delivered > 0) ...<pw.Widget>[
                      _ticketTwoCol('LLIURAT', delivered.toStringAsFixed(2)),
                      _ticketTwoCol('CANVI', change.toStringAsFixed(2)),
                    ],
                    _ticketTwoCol(detail.paymentMethod.toUpperCase(), detail.totalPrice.toStringAsFixed(2)),
                    pw.SizedBox(height: 6),
                    pw.Divider(),
                    pw.Row(
                      mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                      children: <pw.Widget>[
                        pw.Expanded(child: pw.Text('BASE', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8))),
                        pw.Expanded(
                          child: pw.Align(
                            alignment: pw.Alignment.center,
                            child: pw.Text('% IVA', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8)),
                          ),
                        ),
                        pw.Expanded(
                          child: pw.Align(
                            alignment: pw.Alignment.centerRight,
                            child: pw.Text('TOTAL IVA', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8)),
                          ),
                        ),
                      ],
                    ),
                    pw.Row(
                      children: <pw.Widget>[
                        pw.Expanded(child: pw.Text(base10.toStringAsFixed(2), style: const pw.TextStyle(fontSize: 8))),
                        pw.Expanded(
                          child: pw.Align(
                            alignment: pw.Alignment.center,
                            child: pw.Text('10,00 %', style: const pw.TextStyle(fontSize: 8)),
                          ),
                        ),
                        pw.Expanded(
                          child: pw.Align(
                            alignment: pw.Alignment.centerRight,
                            child: pw.Text(iva10.toStringAsFixed(2), style: const pw.TextStyle(fontSize: 8)),
                          ),
                        ),
                      ],
                    ),
                    pw.SizedBox(height: 14),
                    pw.Center(
                      child: pw.Text(
                        'GRÀCIES PER LA SEVA VISITA',
                        style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9),
                      ),
                    ),
                  ],
                );
              },
            ),
          );
          return doc.save();
        },
      );
    } catch (_) {}
  }

  pw.Widget _ticketTwoCol(String left, String right, {bool bold = false, double fontSize = 10}) {
    final pw.TextStyle style = pw.TextStyle(
      fontSize: fontSize,
      fontWeight: bold ? pw.FontWeight.bold : pw.FontWeight.normal,
    );
    return pw.Row(
      mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
      children: <pw.Widget>[
        pw.Text(left, style: style),
        pw.Text(right, style: style),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final double width = MediaQuery.of(context).size.width;
    final bool compact = width < 1200;
    final double ticketWidth = compact ? 340 : 410;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: <Color>[Color(0xFFF6F8FF), Color(0xFFF0F3FC)],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: <Widget>[
                _buildSideBar(),
                const SizedBox(width: 12),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.85),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: const Color(0xFFE4E8F4)),
                      boxShadow: const <BoxShadow>[
                        BoxShadow(color: Color(0x10000000), blurRadius: 20, offset: Offset(0, 8)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: <Widget>[
                        Row(
                          children: <Widget>[
                            Expanded(
                              child: Text(
                                'Hola, ${widget.userName}',
                                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 30),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0F3FF),
                                borderRadius: BorderRadius.circular(999),
                                border: Border.all(color: const Color(0xFFD9E1FA)),
                              ),
                              child: Text(
                                '${_filteredProducts.length} productes',
                                style: const TextStyle(fontWeight: FontWeight.w700, color: TpvTheme.textSecondary),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        _buildCategories(),
                        const SizedBox(height: 14),
                        Expanded(child: _buildProductsBody(compact: compact)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                _buildTicket(width: ticketWidth),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSideBar() {
    return Container(
      width: 90,
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.86),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x12000000), blurRadius: 16, offset: Offset(0, 6)),
        ],
      ),
      child: Column(
        children: <Widget>[
          const SizedBox(height: 16),
          _sideIcon(Icons.grid_view_rounded, active: true, onTap: () {}),
          _sideIcon(
            Icons.shopping_bag_rounded,
            badge: _pendingUrgentCount > 0 ? '$_pendingUrgentCount' : null,
            onTap: _openPendingPreordersPage,
          ),
          _sideIcon(
            Icons.pause_circle_outline_rounded,
            badge: _parkedTickets.isNotEmpty ? '${_parkedTickets.length}' : null,
            onTap: _openParkedTicketsDialog,
          ),
          _sideIcon(Icons.lock_outline_rounded, onTap: _openAdminPinDialog),
          const Spacer(),
          IconButton(
            onPressed: _loggingOut ? null : _logout,
            icon: const Icon(Icons.logout_rounded, color: TpvTheme.danger),
          ),
          const SizedBox(height: 10),
        ],
      ),
    );
  }

  Widget _sideIcon(
    IconData icon, {
    required VoidCallback onTap,
    bool active = false,
    String? badge,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Stack(
          children: <Widget>[
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                gradient: active
                    ? const LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: <Color>[Color(0xFF5D7FE7), TpvTheme.primary],
                      )
                    : null,
                color: active ? null : Colors.transparent,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: active ? Colors.transparent : const Color(0xFFDDE3F2)),
                boxShadow: active
                    ? const <BoxShadow>[
                        BoxShadow(color: Color(0x334E73DF), blurRadius: 14, offset: Offset(0, 6)),
                      ]
                    : null,
              ),
              child: Icon(icon, color: active ? Colors.white : TpvTheme.textSecondary, size: 26),
            ),
            if (badge != null)
              Positioned(
                right: -2,
                top: -2,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(color: TpvTheme.danger, borderRadius: BorderRadius.circular(12)),
                  child: Text(badge, style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800)),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildCategories() {
    return SizedBox(
      height: 56,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _categories.length,
        separatorBuilder: (BuildContext context, int index) => const SizedBox(width: 10),
        itemBuilder: (_, int i) {
          final TpvCategory c = _categories[i];
          final bool active = c.id == _selectedCategory;
          return InkWell(
            borderRadius: BorderRadius.circular(18),
            onTap: () => setState(() => _selectedCategory = c.id),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
              decoration: BoxDecoration(
                gradient: active
                    ? const LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: <Color>[Color(0xFF5D7FE7), TpvTheme.primary],
                      )
                    : null,
                color: active ? null : Colors.white,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: active ? Colors.transparent : const Color(0xFFE0E6F3)),
                boxShadow: active
                    ? const <BoxShadow>[
                        BoxShadow(color: Color(0x2C4E73DF), blurRadius: 14, offset: Offset(0, 6)),
                      ]
                    : null,
              ),
              child: Text(
                c.name,
                style: TextStyle(fontWeight: FontWeight.w700, color: active ? Colors.white : TpvTheme.textMain),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildProductsBody({required bool compact}) {
    if (_loadingCatalog) return const Center(child: CircularProgressIndicator());
    if (_catalogError != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Text('No s\'ha pogut carregar el catàleg'),
            const SizedBox(height: 6),
            Text(_catalogError!, textAlign: TextAlign.center),
            const SizedBox(height: 8),
            OutlinedButton(onPressed: _loadCatalog, child: const Text('Reintentar')),
          ],
        ),
      );
    }
    if (_filteredProducts.isEmpty) return const Center(child: Text('No hi ha productes'));

    return GridView.builder(
      itemCount: _filteredProducts.length,
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: compact ? 2 : 4,
        childAspectRatio: compact ? 0.78 : 0.88,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemBuilder: (_, int index) {
        final TpvProduct product = _filteredProducts[index];
        final int qty = _qtyForProduct(product);
        final int? stockLeft = _remainingStock(product);
        return Card(
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            borderRadius: BorderRadius.circular(18),
            onTap: () => _handleAddProduct(product),
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: product.imageUrl != null && product.imageUrl!.isNotEmpty
                          ? Image.network(product.imageUrl!, fit: BoxFit.cover, width: double.infinity)
                          : Container(
                              color: const Color(0xFFF8F9FE),
                              width: double.infinity,
                              child: const Icon(Icons.fastfood_rounded, color: TpvTheme.primary, size: 36),
                            ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(product.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                  Text('${product.price.toStringAsFixed(2)}€', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                  if (stockLeft != null)
                    Text(
                      stockLeft <= 0 ? 'Esgotat' : '$stockLeft restants',
                      style: TextStyle(color: stockLeft <= 0 ? TpvTheme.danger : TpvTheme.textSecondary, fontSize: 12),
                    ),
                  const SizedBox(height: 8),
                  Row(
                    children: <Widget>[
                      Expanded(
                        child: OutlinedButton(
                          onPressed: qty <= 0 ? null : () => _removeOneFromProduct(product),
                          child: const Text('-'),
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        child: Text('$qty', style: const TextStyle(fontWeight: FontWeight.w800)),
                      ),
                      Expanded(
                        child: FilledButton(
                          onPressed: () => _handleAddProduct(product),
                          child: const Text('+'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  void _removeOneFromProduct(TpvProduct product) {
    final CartItem? item = _cartItems.where((CartItem i) => i.product.id == product.id).cast<CartItem?>().firstWhere(
          (CartItem? i) => i != null,
          orElse: () => null,
        );
    if (item == null) return;
    _changeQty(item.product, -1, notes: item.notes);
  }

  Widget _buildTicket({required double width}) {
    return Container(
      width: width,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.9),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x12000000), blurRadius: 16, offset: Offset(0, 6)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              const Expanded(
                child: Text('Ordre actual', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 28)),
              ),
              if (_cart.isNotEmpty)
                IconButton(
                  onPressed: _clearCart,
                  icon: const Icon(Icons.delete_outline, color: TpvTheme.danger),
                ),
            ],
          ),
          const SizedBox(height: 8),
          Expanded(
            child: _cart.isEmpty
                ? const Center(child: Text('No hi ha productes al ticket'))
                : ListView(
                    children: _cartItems.map((CartItem item) {
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(10),
                          color: const Color(0xFFF8F9FE),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: <Widget>[
                            Row(
                              children: <Widget>[
                                Expanded(child: Text(item.product.name, style: const TextStyle(fontWeight: FontWeight.w700))),
                                IconButton(
                                  onPressed: () => _changeQty(item.product, -item.quantity, notes: item.notes),
                                  icon: const Icon(Icons.delete_outline, color: TpvTheme.danger),
                                ),
                              ],
                            ),
                            if (item.notes != null && item.notes!.isNotEmpty)
                              Text(item.notes!, style: const TextStyle(color: TpvTheme.textSecondary, fontSize: 12)),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: <Widget>[
                                Row(
                                  children: <Widget>[
                                    IconButton(
                                      onPressed: () => _changeQty(item.product, -1, notes: item.notes),
                                      icon: const Icon(Icons.remove_circle_outline),
                                    ),
                                    Text('${item.quantity}', style: const TextStyle(fontWeight: FontWeight.w800)),
                                    IconButton(
                                      onPressed: () => _changeQty(item.product, 1, notes: item.notes),
                                      icon: const Icon(Icons.add_circle_outline),
                                      color: TpvTheme.primary,
                                    ),
                                  ],
                                ),
                                Text('${item.lineTotal.toStringAsFixed(2)}€', style: const TextStyle(fontWeight: FontWeight.w800)),
                              ],
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
          ),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              color: const Color(0xFFF8F9FE),
            ),
            child: Column(
              children: <Widget>[
                _summaryRow('Base imposable', _subTotal),
                _summaryRow('IVA (21%)', _iva),
                const Divider(height: 16),
                _summaryRow('Total', _total, total: true),
              ],
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed: _cart.isEmpty || _submittingOrder ? null : _openCheckoutDialog,
              icon: const Icon(Icons.paid),
              label: Text(_submittingOrder ? 'Processant...' : 'Venda'),
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _cart.isEmpty || _submittingOrder ? null : _openPreorderDialog,
              icon: const Icon(Icons.edit_note),
              label: const Text('Encàrrec'),
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _cart.isEmpty || _submittingOrder ? null : _parkCurrentTicket,
              icon: const Icon(Icons.pause_circle_outline),
              label: const Text('Aparcar Ticket'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _summaryRow(String label, double value, {bool total = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: <Widget>[
        Text(label, style: TextStyle(fontWeight: total ? FontWeight.w900 : FontWeight.w500)),
        Text(
          '${value.toStringAsFixed(2)}€',
          style: TextStyle(fontWeight: total ? FontWeight.w900 : FontWeight.w600, fontSize: total ? 26 : 16),
        ),
      ],
    );
  }

  Widget _workerTouchPill({
    required String label,
    required bool active,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(22),
      child: Container(
        width: 150,
        height: 54,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(18),
          color: active ? TpvTheme.primary : Colors.white,
          border: Border.all(color: const Color(0xFFD9DCE8)),
          boxShadow: active
              ? const <BoxShadow>[
                  BoxShadow(color: Color(0x334E73DF), blurRadius: 14, offset: Offset(0, 6)),
                ]
              : null,
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
              color: active ? Colors.white : Colors.black87,
            ),
          ),
        ),
      ),
    );
  }

  Widget _timeAdjustButton(String label, VoidCallback onTap) {
    return OutlinedButton(
      onPressed: onTap,
      style: OutlinedButton.styleFrom(
        minimumSize: const Size(78, 40),
        visualDensity: VisualDensity.compact,
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      ),
      child: Text(label, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
    );
  }

  void _adjustPreorderTime({int minutesDelta = 0, bool now = false}) {
    DateTime base;
    if (now || _preorderTimeController.text.trim().isEmpty) {
      base = DateTime.now();
    } else {
      final List<String> parts = _preorderTimeController.text.split(':');
      final int h = int.tryParse(parts.first) ?? DateTime.now().hour;
      final int m = int.tryParse(parts.last) ?? DateTime.now().minute;
      final DateTime n = DateTime.now();
      base = DateTime(n.year, n.month, n.day, h, m);
    }
    final DateTime roundedBase = _roundUpToQuarter(base);
    final DateTime updated = now ? roundedBase : _roundUpToQuarter(roundedBase.add(Duration(minutes: minutesDelta)));
    _preorderTimeController.text = _formatTime(updated);
  }

  DateTime _roundUpToQuarter(DateTime date) {
    final DateTime clean = DateTime(date.year, date.month, date.day, date.hour, date.minute);
    final int mod = clean.minute % 15;
    if (mod == 0) return clean;
    return clean.add(Duration(minutes: 15 - mod));
  }

  String _formatTime(DateTime date) {
    return '${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}';
  }

  String _normalizePickupTime(String? raw) {
    if (raw == null) return '';
    final String trimmed = raw.trim();
    if (trimmed.isEmpty) return '';
    final List<String> parts = trimmed.split(':');
    if (parts.length < 2) return trimmed;
    final String hh = parts[0].padLeft(2, '0');
    final String mm = parts[1].padLeft(2, '0');
    return '$hh:$mm';
  }

  TpvProduct? _findProductById(int id) {
    for (final TpvProduct p in _products) {
      if (p.id == id) return p;
    }
    return null;
  }

  TpvProduct? _findProductByName(String name) {
    for (final TpvProduct p in _products) {
      if (p.name == name) return p;
    }
    return null;
  }
}
