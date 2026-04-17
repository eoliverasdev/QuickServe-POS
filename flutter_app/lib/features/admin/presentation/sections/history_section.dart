import 'package:flutter/material.dart';

import '../../../../core/network/api_client.dart';
import '../../../../core/theme/tpv_theme.dart';
import '../../../auth/data/auth_service.dart';
import '../../data/admin_service.dart';
import '../../domain/admin_models.dart';

class HistorySection extends StatefulWidget {
  const HistorySection({super.key, required this.authService});

  final AuthService authService;

  @override
  State<HistorySection> createState() => _HistorySectionState();
}

class _HistorySectionState extends State<HistorySection> {
  late final AdminService _service = AdminService(ApiClient(), widget.authService);

  AdminOrdersPage? _page;
  List<AdminWorker> _workers = <AdminWorker>[];
  bool _loading = true;
  bool _busy = false;
  Object? _error;

  int _currentPage = 1;
  static const int _perPage = 20;

  String? _statusFilter;
  String? _paymentFilter;
  int? _workerFilter;
  DateTime? _from;
  DateTime? _to;
  final TextEditingController _searchController = TextEditingController();

  static const List<String> _statuses = <String>['Pagat', 'Pendent', 'Anullat', 'Encarrec'];
  static const List<String> _payments = <String>['Efectiu', 'Targeta', 'Mixta'];

  @override
  void initState() {
    super.initState();
    _bootstrap();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _bootstrap() async {
    try {
      final List<AdminWorker> workers = await _service.fetchWorkers();
      if (!mounted) return;
      setState(() => _workers = workers);
    } catch (_) {}
    await _load();
  }

  Future<void> _load({int? page}) async {
    setState(() {
      _loading = true;
      _error = null;
      if (page != null) _currentPage = page;
    });
    try {
      final AdminOrdersPage data = await _service.fetchOrders(
        page: _currentPage,
        perPage: _perPage,
        status: _statusFilter,
        paymentMethod: _paymentFilter,
        workerId: _workerFilter,
        from: _from,
        to: _to,
        search: _searchController.text.trim().isEmpty ? null : _searchController.text.trim(),
      );
      if (!mounted) return;
      setState(() {
        _page = data;
        _currentPage = data.currentPage;
      });
    } catch (err) {
      if (!mounted) return;
      setState(() => _error = err);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _resetFilters() {
    setState(() {
      _statusFilter = null;
      _paymentFilter = null;
      _workerFilter = null;
      _from = null;
      _to = null;
      _searchController.clear();
    });
    _load(page: 1);
  }

  Future<void> _pickRange() async {
    final DateTimeRange? range = await showDateRangePicker(
      context: context,
      firstDate: DateTime.now().subtract(const Duration(days: 365 * 3)),
      lastDate: DateTime.now(),
      initialDateRange: _from != null && _to != null
          ? DateTimeRange(start: _from!, end: _to!)
          : null,
    );
    if (range != null) {
      setState(() {
        _from = range.start;
        _to = range.end;
      });
      _load(page: 1);
    }
  }

  Future<void> _openDetail(AdminOrderSummary order) async {
    await showDialog<void>(
      context: context,
      builder: (_) => _OrderDetailDialog(service: _service, orderId: order.id),
    );
  }

  Future<void> _confirmDelete(AdminOrderSummary o) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Eliminar comanda'),
        content: Text('Eliminar la comanda #${o.id}? Aquesta acció no es pot desfer.'),
        actions: <Widget>[
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel·lar'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: FilledButton.styleFrom(backgroundColor: TpvTheme.danger),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    setState(() => _busy = true);
    final ScaffoldMessengerState messenger = ScaffoldMessenger.of(context);
    try {
      await _service.deleteOrder(o.id);
      messenger.showSnackBar(SnackBar(content: Text('Comanda #${o.id} eliminada')));
      await _load();
    } catch (err) {
      messenger.showSnackBar(SnackBar(content: Text('$err')));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        _buildFilters(),
        const SizedBox(height: 12),
        Expanded(child: _buildBody()),
        if (_page != null && _page!.lastPage > 1) ...<Widget>[
          const SizedBox(height: 8),
          _buildPagination(),
        ],
      ],
    );
  }

  Widget _buildFilters() {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x0F000000), blurRadius: 14, offset: Offset(0, 5)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: <Widget>[
          Row(
            children: <Widget>[
              const Icon(Icons.filter_list_rounded, color: TpvTheme.primary),
              const SizedBox(width: 8),
              const Text(
                'Filtres',
                style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
              ),
              const Spacer(),
              TextButton.icon(
                onPressed: _resetFilters,
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('Netejar'),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: <Widget>[
              SizedBox(
                width: 240,
                child: TextField(
                  controller: _searchController,
                  onSubmitted: (_) => _load(page: 1),
                  decoration: const InputDecoration(
                    hintText: 'Buscar per client / nº tiquet',
                    prefixIcon: Icon(Icons.search_rounded),
                    isDense: true,
                  ),
                ),
              ),
              SizedBox(
                width: 180,
                child: DropdownButtonFormField<String?>(
                  initialValue: _statusFilter,
                  decoration: const InputDecoration(labelText: 'Estat', isDense: true),
                  items: <DropdownMenuItem<String?>>[
                    const DropdownMenuItem<String?>(value: null, child: Text('Tots')),
                    ..._statuses.map((String s) => DropdownMenuItem<String?>(value: s, child: Text(s))),
                  ],
                  onChanged: (String? v) {
                    setState(() => _statusFilter = v);
                    _load(page: 1);
                  },
                ),
              ),
              SizedBox(
                width: 180,
                child: DropdownButtonFormField<String?>(
                  initialValue: _paymentFilter,
                  decoration: const InputDecoration(labelText: 'Mètode pagament', isDense: true),
                  items: <DropdownMenuItem<String?>>[
                    const DropdownMenuItem<String?>(value: null, child: Text('Tots')),
                    ..._payments.map((String s) => DropdownMenuItem<String?>(value: s, child: Text(s))),
                  ],
                  onChanged: (String? v) {
                    setState(() => _paymentFilter = v);
                    _load(page: 1);
                  },
                ),
              ),
              SizedBox(
                width: 200,
                child: DropdownButtonFormField<int?>(
                  initialValue: _workerFilter,
                  decoration: const InputDecoration(labelText: 'Treballador', isDense: true),
                  items: <DropdownMenuItem<int?>>[
                    const DropdownMenuItem<int?>(value: null, child: Text('Tots')),
                    ..._workers.map((AdminWorker w) => DropdownMenuItem<int?>(value: w.id, child: Text(w.name))),
                  ],
                  onChanged: (int? v) {
                    setState(() => _workerFilter = v);
                    _load(page: 1);
                  },
                ),
              ),
              OutlinedButton.icon(
                onPressed: _pickRange,
                icon: const Icon(Icons.calendar_today_rounded, size: 18),
                label: Text(
                  _from != null && _to != null
                      ? '${_short(_from!)} → ${_short(_to!)}'
                      : 'Rang de dates',
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_loading && _page == null) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Icon(Icons.error_outline, size: 48, color: TpvTheme.danger),
            const SizedBox(height: 8),
            Text('$_error', textAlign: TextAlign.center),
            const SizedBox(height: 10),
            OutlinedButton(onPressed: () => _load(), child: const Text('Reintentar')),
          ],
        ),
      );
    }
    final List<AdminOrderSummary> orders = _page?.orders ?? <AdminOrderSummary>[];
    if (orders.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Icon(Icons.receipt_long_rounded, size: 56, color: Color(0xFFB0B6C9)),
            const SizedBox(height: 10),
            const Text(
              'No s\'han trobat comandes',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: TpvTheme.textMain),
            ),
            const SizedBox(height: 6),
            const Text(
              'Prova amb altres filtres o un rang de dates diferent.',
              style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600),
            ),
          ],
        ),
      );
    }
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x0F000000), blurRadius: 14, offset: Offset(0, 5)),
        ],
      ),
      child: RefreshIndicator(
        onRefresh: () => _load(),
        child: ListView.separated(
          padding: const EdgeInsets.symmetric(vertical: 6),
          itemCount: orders.length,
          separatorBuilder: (_, _) => const Divider(height: 1, color: Color(0xFFEDF0F8)),
          itemBuilder: (BuildContext context, int i) {
            final AdminOrderSummary o = orders[i];
            return _OrderRow(
              order: o,
              onTap: () => _openDetail(o),
              onDelete: _busy ? null : () => _confirmDelete(o),
            );
          },
        ),
      ),
    );
  }

  Widget _buildPagination() {
    final int cp = _page!.currentPage;
    final int lp = _page!.lastPage;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE4E8F4)),
      ),
      child: Row(
        children: <Widget>[
          Expanded(
            child: Text(
              'Pàgina $cp de $lp · ${_page!.total} comandes',
              style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700),
            ),
          ),
          IconButton(
            onPressed: cp > 1 ? () => _load(page: cp - 1) : null,
            icon: const Icon(Icons.chevron_left_rounded),
          ),
          IconButton(
            onPressed: cp < lp ? () => _load(page: cp + 1) : null,
            icon: const Icon(Icons.chevron_right_rounded),
          ),
        ],
      ),
    );
  }

  static String _short(DateTime dt) => '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}';
}

class _OrderRow extends StatelessWidget {
  const _OrderRow({required this.order, required this.onTap, required this.onDelete});

  final AdminOrderSummary order;
  final VoidCallback onTap;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    final Color statusColor = _statusColor(order.status);
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(
          children: <Widget>[
            Container(
              width: 56,
              alignment: Alignment.center,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: <Widget>[
                  Text(
                    '#${order.id}',
                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: TpvTheme.textSecondary),
                  ),
                  if (order.pickupNumber != null && order.pickupNumber!.isNotEmpty)
                    Text(
                      'E${order.pickupNumber}',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 11, color: Color(0xFFF59E0B)),
                    ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Row(
                    children: <Widget>[
                      Flexible(
                        child: Text(
                          order.customerName?.isNotEmpty == true
                              ? order.customerName!
                              : order.fiscalFullNumber ?? 'Comanda #${order.id}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: statusColor.withValues(alpha: 0.14),
                          borderRadius: BorderRadius.circular(999),
                          border: Border.all(color: statusColor.withValues(alpha: 0.3)),
                        ),
                        child: Text(
                          order.status,
                          style: TextStyle(color: statusColor, fontWeight: FontWeight.w900, fontSize: 10),
                        ),
                      ),
                      if (order.isPreorder)
                        Padding(
                          padding: const EdgeInsets.only(left: 6),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFFF4E0),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: const Text(
                              'Encàrrec',
                              style: TextStyle(color: Color(0xFFD97706), fontWeight: FontWeight.w900, fontSize: 10),
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(
                    _buildSubtitle(order),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 12),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: <Widget>[
                Text(
                  '${order.totalPrice.toStringAsFixed(2)}€',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: TpvTheme.primary),
                ),
                Text(
                  '${order.itemsCount} art · ${order.paymentMethod ?? '-'}',
                  style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 11),
                ),
              ],
            ),
            IconButton(
              onPressed: onDelete,
              icon: const Icon(Icons.delete_outline, color: TpvTheme.danger),
              tooltip: 'Eliminar',
            ),
          ],
        ),
      ),
    );
  }

  String _buildSubtitle(AdminOrderSummary o) {
    final List<String> parts = <String>[];
    if (o.createdAt != null) {
      parts.add(_formatDateTime(o.createdAt!));
    }
    if (o.workerName != null && o.workerName!.isNotEmpty) {
      parts.add(o.workerName!);
    }
    if (o.pickupTime != null && o.pickupTime!.isNotEmpty) {
      parts.add('Recollida ${o.pickupTime}');
    }
    return parts.join(' · ');
  }

  static String _formatDateTime(DateTime dt) {
    final DateTime local = dt.toLocal();
    final String d = '${local.day.toString().padLeft(2, '0')}/${local.month.toString().padLeft(2, '0')}/${local.year}';
    final String h = '${local.hour.toString().padLeft(2, '0')}:${local.minute.toString().padLeft(2, '0')}';
    return '$d · $h';
  }

  static Color _statusColor(String status) {
    switch (status) {
      case 'Pagat':
        return const Color(0xFF1C8B43);
      case 'Anullat':
        return TpvTheme.danger;
      case 'Encarrec':
        return const Color(0xFFD97706);
      default:
        return const Color(0xFF3B82F6);
    }
  }
}

class _OrderDetailDialog extends StatefulWidget {
  const _OrderDetailDialog({required this.service, required this.orderId});

  final AdminService service;
  final int orderId;

  @override
  State<_OrderDetailDialog> createState() => _OrderDetailDialogState();
}

class _OrderDetailDialogState extends State<_OrderDetailDialog> {
  AdminOrderDetail? _detail;
  bool _loading = true;
  Object? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final AdminOrderDetail d = await widget.service.fetchOrderDetail(widget.orderId);
      if (!mounted) return;
      setState(() {
        _detail = d;
        _loading = false;
      });
    } catch (err) {
      if (!mounted) return;
      setState(() {
        _error = err;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 540, maxHeight: 640),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(22, 18, 22, 14),
          child: _buildBody(),
        ),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            const Icon(Icons.error_outline, size: 48, color: TpvTheme.danger),
            const SizedBox(height: 8),
            Text('$_error', textAlign: TextAlign.center),
            const SizedBox(height: 10),
            FilledButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Tancar')),
          ],
        ),
      );
    }
    final AdminOrderSummary s = _detail!.summary;
    final List<AdminOrderItem> items = _detail!.items;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        Row(
          children: <Widget>[
            Expanded(
              child: Text(
                s.fiscalFullNumber ?? 'Comanda #${s.id}',
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 20),
              ),
            ),
            IconButton(
              onPressed: () => Navigator.of(context).pop(),
              icon: const Icon(Icons.close_rounded),
            ),
          ],
        ),
        if (s.createdAt != null)
          Text(
            _formatDateTime(s.createdAt!),
            style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600),
          ),
        const SizedBox(height: 10),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: <Widget>[
            _metaChip('Estat', s.status),
            if (s.paymentMethod != null) _metaChip('Mètode', s.paymentMethod!),
            if (s.workerName != null) _metaChip('Treballador', s.workerName!),
            if (s.customerName != null && s.customerName!.isNotEmpty) _metaChip('Client', s.customerName!),
            if (s.pickupTime != null && s.pickupTime!.isNotEmpty) _metaChip('Recollida', s.pickupTime!),
          ],
        ),
        const SizedBox(height: 14),
        const Text('Productes', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
        const SizedBox(height: 6),
        Expanded(
          child: items.isEmpty
              ? const Center(child: Text('Sense productes'))
              : ListView.separated(
                  itemCount: items.length,
                  separatorBuilder: (_, _) => const Divider(height: 1, color: Color(0xFFEDF0F8)),
                  itemBuilder: (BuildContext _, int i) {
                    final AdminOrderItem it = items[i];
                    return Padding(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      child: Row(
                        children: <Widget>[
                          Container(
                            width: 32,
                            height: 32,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: TpvTheme.primary.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              '×${it.quantity}',
                              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: TpvTheme.primary),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              it.name,
                              style: const TextStyle(fontWeight: FontWeight.w700),
                            ),
                          ),
                          Text(
                            '${it.subtotal.toStringAsFixed(2)}€',
                            style: const TextStyle(fontWeight: FontWeight.w900),
                          ),
                        ],
                      ),
                    );
                  },
                ),
        ),
        const Divider(height: 20),
        Row(
          children: <Widget>[
            const Text('Total', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
            const Spacer(),
            Text(
              '${s.totalPrice.toStringAsFixed(2)}€',
              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: TpvTheme.primary),
            ),
          ],
        ),
      ],
    );
  }

  Widget _metaChip(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: const Color(0xFFF4F7FF),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: const Color(0xFFE1E6F5)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: <Widget>[
          Text(
            '$label: ',
            style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700, fontSize: 12),
          ),
          Text(
            value,
            style: const TextStyle(color: TpvTheme.textMain, fontWeight: FontWeight.w800, fontSize: 12),
          ),
        ],
      ),
    );
  }

  String _formatDateTime(DateTime dt) {
    final DateTime local = dt.toLocal();
    final String d = '${local.day.toString().padLeft(2, '0')}/${local.month.toString().padLeft(2, '0')}/${local.year}';
    final String h = '${local.hour.toString().padLeft(2, '0')}:${local.minute.toString().padLeft(2, '0')}';
    return '$d · $h';
  }
}
