import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/tpv_theme.dart';
import '../../auth/data/auth_service.dart';
import '../data/tpv_catalog_service.dart';
import '../data/tpv_sales_service.dart';
import '../domain/tpv_models.dart';

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
  static const double _bagUnitPrice = 0.10;
  static const int _bagMaxCount = 50;

  List<TpvCategory> _categories = <TpvCategory>[TpvCategory(id: 'all', name: 'Tots')];
  List<TpvProduct> _products = <TpvProduct>[];

  String _selectedCategory = 'all';
  final Map<int, CartItem> _cart = <int, CartItem>{};
  bool _loggingOut = false;
  bool _loadingCatalog = true;
  bool _submittingOrder = false;
  String? _catalogError;
  List<TpvWorker> _workers = <TpvWorker>[];
  String? _paymentMethod;
  int? _selectedWorkerId;
  int _bagCountPayment = 0;
  bool _isDiscountAppliedPayment = false;
  final TextEditingController _cashGivenController = TextEditingController();
  final TextEditingController _preorderCustomerController = TextEditingController();
  final TextEditingController _preorderTimeController = TextEditingController();
  List<TpvPreorder> _pendingPreorders = <TpvPreorder>[];

  @override
  void initState() {
    super.initState();
    _loadCatalog();
  }

  List<TpvProduct> get _filteredProducts {
    if (_selectedCategory == 'all') {
      return _products;
    }
    return _products.where((TpvProduct p) => p.categoryIds.contains(_selectedCategory)).toList();
  }

  // Prices already include IVA, like the current web TPV.
  double get _total => _cart.values.fold(0, (double sum, CartItem item) => sum + item.lineTotal);
  double get _subTotal => _total / 1.21;
  double get _iva => _total - _subTotal;
  double get _productsTotalWithDiscount => _isDiscountAppliedPayment ? _total * 0.85 : _total;
  double get _finalPaymentTotal => _productsTotalWithDiscount + (_bagCountPayment * _bagUnitPrice);
  double get _finalPaymentBase => _finalPaymentTotal / 1.21;
  double get _finalPaymentIva => _finalPaymentTotal - _finalPaymentBase;

  void _changeQty(TpvProduct product, int delta) {
    final CartItem? current = _cart[product.id];
    final int nextQty = (current?.quantity ?? 0) + delta;

    setState(() {
      if (nextQty <= 0) {
        _cart.remove(product.id);
      } else {
        _cart[product.id] = CartItem(product: product, quantity: nextQty);
      }
    });
  }

  void _clearCart() {
    setState(() {
      _cart.clear();
    });
  }

  Future<void> _logout() async {
    setState(() => _loggingOut = true);
    await widget.onLogout();
  }

  Future<void> _loadCatalog() async {
    setState(() {
      _loadingCatalog = true;
      _catalogError = null;
    });

    try {
      final TpvCatalogData data = await TpvCatalogService(
        ApiClient(),
        widget.authService,
      ).fetchCatalog();

      if (!mounted) return;
      setState(() {
        _categories = data.categories;
        _products = data.products;
      });
    } catch (error) {
      if (!mounted) return;
      setState(() {
        _catalogError = error.toString();
      });
    } finally {
      if (mounted) {
        setState(() {
          _loadingCatalog = false;
        });
      }
    }
  }

  @override
  void dispose() {
    _cashGivenController.dispose();
    _preorderCustomerController.dispose();
    _preorderTimeController.dispose();
    super.dispose();
  }

  Future<void> _openCheckoutDialog() async {
    if (_cart.isEmpty || _submittingOrder) return;

    final TpvSalesService salesService = TpvSalesService(ApiClient(), widget.authService);
    if (_workers.isEmpty) {
      try {
        final List<TpvWorker> workers = await salesService.fetchWorkers();
        if (!mounted) return;
        setState(() {
          _workers = workers;
          _selectedWorkerId ??= workers.isNotEmpty ? workers.first.id : null;
        });
      } catch (error) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No s\'han pogut carregar treballadors: $error')),
        );
        return;
      }
    }

    if (!mounted) return;
    _paymentMethod = null;
    _bagCountPayment = 0;
    _isDiscountAppliedPayment = false;
    _cashGivenController.clear();

    await showDialog<void>(
      context: context,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (BuildContext context, void Function(void Function()) setModalState) {
            final double cashGiven = _parseMoneyInput(_cashGivenController.text);
            final bool insufficientCash =
                _paymentMethod == 'Efectiu' && cashGiven > 0 && cashGiven < _finalPaymentTotal;
            final double changeAmount = (cashGiven - _finalPaymentTotal).clamp(0, double.infinity);
            final bool canConfirm = _selectedWorkerId != null &&
                _paymentMethod != null &&
                !_submittingOrder &&
                !insufficientCash;

            return Dialog(
              insetPadding: const EdgeInsets.all(24),
              backgroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 560, maxHeight: 760),
                child: Padding(
                  padding: const EdgeInsets.all(28),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      const Text('Resum del pagament', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 18),
                      Expanded(
                        child: SingleChildScrollView(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: <Widget>[
                              const Text('PRODUCTES DE LA COMANDA',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: TpvTheme.textSecondary)),
                              const SizedBox(height: 8),
                              Container(
                                constraints: const BoxConstraints(maxHeight: 150),
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF8F9FE),
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                child: SingleChildScrollView(
                                  child: Column(
                                    children: _cart.values.map((CartItem item) {
                                      return Padding(
                                        padding: const EdgeInsets.symmetric(vertical: 3),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: <Widget>[
                                            Expanded(
                                              child: Text(
                                                '${item.quantity}x ${item.product.name}',
                                                style: const TextStyle(color: Color(0xFF555555), fontWeight: FontWeight.w600),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                            Text('${item.lineTotal.toStringAsFixed(2)}€',
                                                style: const TextStyle(fontWeight: FontWeight.w700)),
                                          ],
                                        ),
                                      );
                                    }).toList(),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 12),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: <Widget>[
                                  const Text('Base imposable', style: TextStyle(color: Color(0xFF888888))),
                                  Text('${_finalPaymentBase.toStringAsFixed(2)}€',
                                      style: const TextStyle(color: Color(0xFF888888), fontWeight: FontWeight.w700)),
                                ],
                              ),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: <Widget>[
                                  const Text('IVA (21%)', style: TextStyle(color: Color(0xFF888888))),
                                  Text('${_finalPaymentIva.toStringAsFixed(2)}€',
                                      style: const TextStyle(color: Color(0xFF888888), fontWeight: FontWeight.w700)),
                                ],
                              ),
                              const SizedBox(height: 10),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: <Widget>[
                                  OutlinedButton.icon(
                                    onPressed: _bagCountPayment >= _bagMaxCount
                                        ? null
                                        : () => setModalState(() => _bagCountPayment++),
                                    icon: const Icon(Icons.shopping_bag_outlined, size: 16),
                                    label: Text('Bossa (+0,10€)${_bagCountPayment > 0 ? ' ×$_bagCountPayment' : ''}'),
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: TpvTheme.primary,
                                      side: const BorderSide(color: TpvTheme.primary),
                                    ),
                                  ),
                                  if (_bagCountPayment > 0)
                                    OutlinedButton(
                                      onPressed: () => setModalState(() => _bagCountPayment--),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: TpvTheme.danger,
                                        side: const BorderSide(color: TpvTheme.danger, width: 1.5),
                                      ),
                                      child: const Text('Treure'),
                                    ),
                                  OutlinedButton(
                                    onPressed: () => setModalState(() => _isDiscountAppliedPayment = !_isDiscountAppliedPayment),
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: Colors.green.shade700,
                                      side: BorderSide(color: Colors.green.shade700),
                                      backgroundColor: _isDiscountAppliedPayment ? Colors.green : Colors.white,
                                    ),
                                    child: Text(
                                      '-15% Treballador',
                                      style: TextStyle(color: _isDiscountAppliedPayment ? Colors.white : Colors.green.shade700),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Align(
                                alignment: Alignment.centerRight,
                                child: Text(
                                  'Total: ${_finalPaymentTotal.toStringAsFixed(2)}€',
                                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 28),
                                ),
                              ),
                              const SizedBox(height: 14),
                              const Text('TREBALLADOR',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: TpvTheme.textSecondary)),
                              const SizedBox(height: 8),
                              DropdownButtonFormField<int>(
                                initialValue: _selectedWorkerId,
                                items: _workers
                                    .map((TpvWorker w) => DropdownMenuItem<int>(value: w.id, child: Text(w.name)))
                                    .toList(),
                                onChanged: (int? value) {
                                  setModalState(() => _selectedWorkerId = value);
                                  setState(() => _selectedWorkerId = value);
                                },
                              ),
                              const SizedBox(height: 16),
                              const Text('MÈTODE DE PAGAMENT',
                                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: TpvTheme.textSecondary)),
                              const SizedBox(height: 8),
                              Row(
                                children: <Widget>[
                                  Expanded(
                                    child: _paymentMethodButton(
                                      label: 'Efectiu',
                                      icon: Icons.payments_outlined,
                                      selected: _paymentMethod == 'Efectiu',
                                      onTap: () {
                                        setModalState(() => _paymentMethod = 'Efectiu');
                                        setState(() => _paymentMethod = 'Efectiu');
                                        _cashGivenController.clear();
                                      },
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: _paymentMethodButton(
                                      label: 'Targeta',
                                      icon: Icons.credit_card,
                                      selected: _paymentMethod == 'Targeta',
                                      onTap: () {
                                        setModalState(() => _paymentMethod = 'Targeta');
                                        setState(() => _paymentMethod = 'Targeta');
                                      },
                                    ),
                                  ),
                                ],
                              ),
                              if (_paymentMethod == 'Efectiu') ...<Widget>[
                                const SizedBox(height: 14),
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF0F8FF),
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(color: const Color(0xFFD0E8FF)),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: <Widget>[
                                      const Text('Import entregat pel client',
                                          style: TextStyle(fontWeight: FontWeight.w800, color: TpvTheme.primary)),
                                      const SizedBox(height: 8),
                                      TextField(
                                        controller: _cashGivenController,
                                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                                        decoration: const InputDecoration(
                                          hintText: '0,00',
                                          suffixText: '€',
                                        ),
                                        onChanged: (_) => setModalState(() {}),
                                      ),
                                      const SizedBox(height: 8),
                                      Wrap(
                                        spacing: 8,
                                        children: <Widget>[
                                          _quickCashButton(10, setModalState),
                                          _quickCashButton(20, setModalState),
                                          _quickCashButton(50, setModalState),
                                          _quickCashButton(100, setModalState),
                                        ],
                                      ),
                                      const SizedBox(height: 10),
                                      if (cashGiven > 0 && !insufficientCash)
                                        Container(
                                          width: double.infinity,
                                          padding: const EdgeInsets.all(12),
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(10),
                                            border: Border.all(color: Colors.green),
                                            color: Colors.white,
                                          ),
                                          child: Text(
                                            'Canvi a retornar: ${changeAmount.toStringAsFixed(2)}€',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w900,
                                              color: Colors.green,
                                              fontSize: 20,
                                            ),
                                          ),
                                        ),
                                      if (insufficientCash)
                                        Container(
                                          width: double.infinity,
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(10),
                                            border: Border.all(color: Colors.red),
                                            color: const Color(0xFFFFF0F0),
                                          ),
                                          child: const Text(
                                            'Import insuficient per cobrir el total.',
                                            style: TextStyle(fontWeight: FontWeight.w700, color: Colors.red),
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 10),
                      SizedBox(
                        width: double.infinity,
                        child: FilledButton(
                          onPressed: !canConfirm
                              ? null
                              : () async {
                                  final NavigatorState navigator = Navigator.of(context);
                                  await _submitOrder();
                                  if (mounted) navigator.pop();
                                },
                          style: FilledButton.styleFrom(
                            backgroundColor: TpvTheme.primary,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: const Text('Confirmar i tancar venda', style: TextStyle(fontWeight: FontWeight.w800)),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Center(
                        child: TextButton(
                          onPressed: _submittingOrder ? null : () => Navigator.of(context).pop(),
                          child: const Text('Tornar'),
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
  }

  Future<void> _submitOrder() async {
    if (_selectedWorkerId == null || _paymentMethod == null) return;

    setState(() => _submittingOrder = true);
    try {
      await TpvSalesService(ApiClient(), widget.authService).createOrder(
        workerId: _selectedWorkerId!,
        paymentMethod: _paymentMethod!,
        cartItems: _cart.values.toList(),
        totalPrice: _finalPaymentTotal,
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Venda realitzada amb èxit')),
      );
      setState(() {
        _cart.clear();
      });
      await _loadCatalog();
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error en finalitzar la venda: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _submittingOrder = false);
      }
    }
  }

  Future<void> _openPreorderDialog() async {
    if (_cart.isEmpty || _submittingOrder) return;
    final TpvSalesService salesService = TpvSalesService(ApiClient(), widget.authService);
    if (_workers.isEmpty) {
      try {
        _workers = await salesService.fetchWorkers();
        _selectedWorkerId ??= _workers.isNotEmpty ? _workers.first.id : null;
      } catch (error) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No s\'han pogut carregar treballadors: $error')),
        );
        return;
      }
    }

    _preorderCustomerController.clear();
    _preorderTimeController.clear();

    if (!mounted) return;
    await showDialog<void>(
      context: context,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (BuildContext context, void Function(void Function()) setModalState) {
            final bool canCreate = _selectedWorkerId != null;
            return AlertDialog(
              title: const Text('Guardar encàrrec'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: <Widget>[
                  DropdownButtonFormField<int>(
                    initialValue: _selectedWorkerId,
                    decoration: const InputDecoration(labelText: 'Treballador'),
                    items: _workers
                        .map((TpvWorker w) => DropdownMenuItem<int>(value: w.id, child: Text(w.name)))
                        .toList(),
                    onChanged: (int? value) {
                      setModalState(() => _selectedWorkerId = value);
                      setState(() => _selectedWorkerId = value);
                    },
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: _preorderTimeController,
                    decoration: const InputDecoration(labelText: 'Hora recollida (opcional, ex: 13:30)'),
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: _preorderCustomerController,
                    decoration: const InputDecoration(labelText: 'Nom client (opcional)'),
                  ),
                ],
              ),
              actions: <Widget>[
                TextButton(
                  onPressed: _submittingOrder ? null : () => Navigator.of(context).pop(),
                  child: const Text('Cancel·lar'),
                ),
                FilledButton(
                  onPressed: !canCreate
                      ? null
                      : () async {
                          final NavigatorState navigator = Navigator.of(context);
                          await _submitPreorder();
                          if (mounted) navigator.pop();
                        },
                  child: const Text('Guardar encàrrec'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  Future<void> _submitPreorder() async {
    if (_selectedWorkerId == null) return;
    setState(() => _submittingOrder = true);
    try {
      await TpvSalesService(ApiClient(), widget.authService).createOrder(
        workerId: _selectedWorkerId!,
        paymentMethod: 'Pendent',
        cartItems: _cart.values.toList(),
        totalPrice: _total,
        isPreorder: true,
        pickupTime: _preorderTimeController.text.trim().isEmpty ? null : _preorderTimeController.text.trim(),
        customerName:
            _preorderCustomerController.text.trim().isEmpty ? null : _preorderCustomerController.text.trim(),
      );
      if (!mounted) return;
      setState(() => _cart.clear());
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Encàrrec guardat correctament')),
      );
      await _loadPendingPreorders();
      await _loadCatalog();
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error guardant encàrrec: $error')),
      );
    } finally {
      if (mounted) setState(() => _submittingOrder = false);
    }
  }

  Future<void> _loadPendingPreorders() async {
    try {
      final List<TpvPreorder> pending = await TpvSalesService(ApiClient(), widget.authService).fetchPendingPreorders();
      if (!mounted) return;
      setState(() => _pendingPreorders = pending);
    } catch (_) {
      // Keep silent in background loads.
    }
  }

  Future<void> _openPendingPreordersDialog() async {
    await _loadPendingPreorders();
    if (!mounted) return;
    showDialog<void>(
      context: context,
      builder: (BuildContext context) {
        return Dialog(
          insetPadding: const EdgeInsets.all(24),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 700, maxHeight: 600),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  const Text('Encàrrecs pendents', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 22)),
                  const SizedBox(height: 14),
                  Expanded(
                    child: _pendingPreorders.isEmpty
                        ? const Center(child: Text('Cap encàrrec pendent'))
                        : ListView.separated(
                            itemCount: _pendingPreorders.length,
                            separatorBuilder: (BuildContext context, int index) => const SizedBox(height: 10),
                            itemBuilder: (BuildContext context, int index) {
                              final TpvPreorder order = _pendingPreorders[index];
                              return Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF8F9FE),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  children: <Widget>[
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: <Widget>[
                                          Text(
                                            'Encàrrec #${order.pickupNumber ?? order.id}',
                                            style: const TextStyle(fontWeight: FontWeight.w800),
                                          ),
                                          Text(
                                            '${order.customerName ?? 'Sense nom'} · ${order.pickupTime ?? '--:--'}',
                                          ),
                                          Text(
                                            '${order.itemsCount} productes · ${order.totalPrice.toStringAsFixed(2)}€',
                                            style: const TextStyle(color: TpvTheme.textSecondary),
                                          ),
                                        ],
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
                      child: const Text('Tancar'),
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

  double _parseMoneyInput(String raw) {
    final String cleaned = raw.trim().replaceAll(',', '.');
    return double.tryParse(cleaned) ?? 0;
  }

  Widget _quickCashButton(int amount, void Function(void Function()) setModalState) {
    return OutlinedButton(
      onPressed: () {
        _cashGivenController.text = amount.toStringAsFixed(2).replaceAll('.', ',');
        setModalState(() {});
      },
      style: OutlinedButton.styleFrom(
        foregroundColor: TpvTheme.primary,
        backgroundColor: Colors.white,
      ),
      child: Text('$amount€'),
    );
  }

  Widget _paymentMethodButton({
    required String label,
    required IconData icon,
    required bool selected,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: selected ? TpvTheme.primary : const Color(0xFFE9EDF7)),
          color: selected ? const Color(0xFFF0F3FF) : Colors.white,
        ),
        child: Column(
          children: <Widget>[
            Icon(icon, color: selected ? TpvTheme.primary : TpvTheme.textSecondary),
            const SizedBox(height: 4),
            Text(label, style: TextStyle(color: selected ? TpvTheme.primary : TpvTheme.textSecondary)),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Row(
        children: <Widget>[
          _buildSideBar(),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text('Hola, ${widget.userName}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 22)),
                  const SizedBox(height: 16),
                  _buildCategories(),
                  const SizedBox(height: 16),
                  Expanded(child: _buildProductsBody()),
                ],
              ),
            ),
          ),
          _buildTicket(),
        ],
      ),
    );
  }

  Widget _buildSideBar() {
    return Container(
      width: 80,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(right: BorderSide(color: Color(0xFFE9EDF7))),
      ),
      child: Column(
        children: <Widget>[
          const SizedBox(height: 20),
          _sideIcon(Icons.grid_view_rounded, active: true),
          InkWell(
            borderRadius: BorderRadius.circular(12),
            onTap: _openPendingPreordersDialog,
            child: _sideIcon(Icons.shopping_bag_rounded),
          ),
          _sideIcon(Icons.pause_circle_outline_rounded),
          const Spacer(),
          IconButton(
            onPressed: _loggingOut ? null : _logout,
            icon: const Icon(Icons.logout_rounded, color: TpvTheme.danger),
          ),
          const SizedBox(height: 12),
        ],
      ),
    );
  }

  Widget _sideIcon(IconData icon, {bool active = false}) {
    return Container(
      width: 48,
      height: 48,
      margin: const EdgeInsets.only(bottom: 18),
      decoration: BoxDecoration(
        color: active ? TpvTheme.primary : Colors.transparent,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Icon(icon, color: active ? Colors.white : TpvTheme.textSecondary),
    );
  }

  Widget _buildCategories() {
    return SizedBox(
      height: 52,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _categories.length,
        separatorBuilder: (BuildContext context, int index) => const SizedBox(width: 10),
        itemBuilder: (BuildContext context, int i) {
          final TpvCategory c = _categories[i];
          final bool active = c.id == _selectedCategory;
          return InkWell(
            borderRadius: BorderRadius.circular(16),
            onTap: () => setState(() => _selectedCategory = c.id),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
              decoration: BoxDecoration(
                color: active ? TpvTheme.primary : Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: active ? TpvTheme.primary : const Color(0xFFE9EDF7)),
              ),
              child: Text(
                c.name,
                style: TextStyle(
                  fontWeight: FontWeight.w700,
                  color: active ? Colors.white : TpvTheme.textMain,
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildProductsGrid() {
    return GridView.builder(
      itemCount: _filteredProducts.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        childAspectRatio: 0.9,
        crossAxisSpacing: 14,
        mainAxisSpacing: 14,
      ),
      itemBuilder: (BuildContext context, int index) {
        final TpvProduct product = _filteredProducts[index];
        final int qty = _cart[product.id]?.quantity ?? 0;
        return Card(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Expanded(
                  child: Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8F9FE),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.fastfood_rounded, size: 36, color: TpvTheme.primary),
                  ),
                ),
                const SizedBox(height: 10),
                Text(product.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700)),
                const SizedBox(height: 6),
                Text('${product.price.toStringAsFixed(2)}€', style: const TextStyle(fontWeight: FontWeight.w800)),
                const SizedBox(height: 8),
                Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8F9FE),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: <Widget>[
                      IconButton(
                        onPressed: () => _changeQty(product, -1),
                        icon: const Icon(Icons.remove),
                      ),
                      Text('$qty', style: const TextStyle(fontWeight: FontWeight.w700)),
                      IconButton(
                        onPressed: () => _changeQty(product, 1),
                        icon: const Icon(Icons.add),
                        color: TpvTheme.primary,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildProductsBody() {
    if (_loadingCatalog) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_catalogError != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Text('No s\'ha pogut carregar el cataleg'),
            const SizedBox(height: 8),
            Text(_catalogError!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            OutlinedButton(onPressed: _loadCatalog, child: const Text('Reintentar')),
          ],
        ),
      );
    }

    if (_filteredProducts.isEmpty) {
      return const Center(child: Text('No hi ha productes en aquesta categoria'));
    }

    return _buildProductsGrid();
  }

  Widget _buildTicket() {
    return Container(
      width: 380,
      padding: const EdgeInsets.all(24),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(left: BorderSide(color: Color(0xFFE9EDF7))),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: <Widget>[
              const Text('Ordre actual', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
              if (_cart.isNotEmpty)
                TextButton.icon(
                  onPressed: _clearCart,
                  icon: const Icon(Icons.delete_sweep_rounded, color: TpvTheme.danger),
                  label: const Text('Buidar', style: TextStyle(color: TpvTheme.danger, fontWeight: FontWeight.w800)),
                ),
            ],
          ),
          const SizedBox(height: 16),
          Expanded(
            child: _cart.isEmpty
                ? const Center(child: Text('No hi ha productes al tiquet'))
                : ListView(
                    children: _cart.values.map((CartItem item) {
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8F9FE),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: <Widget>[
                            Row(
                              children: <Widget>[
                                Expanded(
                                  child: Text(
                                    item.product.name,
                                    style: const TextStyle(fontWeight: FontWeight.w700),
                                  ),
                                ),
                                IconButton(
                                  onPressed: () => _changeQty(item.product, -item.quantity),
                                  icon: const Icon(Icons.delete_outline, color: TpvTheme.danger),
                                  tooltip: 'Eliminar línia',
                                ),
                              ],
                            ),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: <Widget>[
                                Row(
                                  children: <Widget>[
                                    IconButton(
                                      onPressed: () => _changeQty(item.product, -1),
                                      icon: const Icon(Icons.remove_circle_outline),
                                      tooltip: 'Treure una unitat',
                                    ),
                                    Text('${item.quantity}', style: const TextStyle(fontWeight: FontWeight.w800)),
                                    IconButton(
                                      onPressed: () => _changeQty(item.product, 1),
                                      icon: const Icon(Icons.add_circle_outline),
                                      color: TpvTheme.primary,
                                      tooltip: 'Afegir una unitat',
                                    ),
                                  ],
                                ),
                                Text(
                                  '${item.lineTotal.toStringAsFixed(2)}€',
                                  style: const TextStyle(fontWeight: FontWeight.w800),
                                ),
                              ],
                            ),
                            Text(
                              '${item.product.price.toStringAsFixed(2)}€ / unitat',
                              style: const TextStyle(fontSize: 12, color: TpvTheme.textSecondary),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
          ),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFF8F9FE),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              children: <Widget>[
                _summaryRow('Subtotal', _subTotal),
                const SizedBox(height: 6),
                _summaryRow('IVA (21%)', _iva),
                const Divider(height: 20),
                _summaryRow('Total', _total, total: true),
              ],
            ),
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: FilledButton(
              onPressed: _cart.isEmpty || _submittingOrder ? null : _openCheckoutDialog,
              style: FilledButton.styleFrom(
                backgroundColor: TpvTheme.primary,
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              child: Text(_submittingOrder ? 'Processant...' : 'Finalitzar comanda'),
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton(
              onPressed: _cart.isEmpty || _submittingOrder ? null : _openPreorderDialog,
              child: const Text('Guardar encàrrec'),
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
        Text(label, style: TextStyle(fontWeight: total ? FontWeight.w800 : FontWeight.w500)),
        Text(
          '${value.toStringAsFixed(2)}€',
          style: TextStyle(
            fontWeight: total ? FontWeight.w800 : FontWeight.w600,
            fontSize: total ? 20 : 14,
          ),
        ),
      ],
    );
  }
}
