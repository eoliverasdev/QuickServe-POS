import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/tpv_theme.dart';
import '../../auth/data/auth_service.dart';
import '../data/tpv_sales_service.dart';
import '../domain/tpv_models.dart';

class PendingPreordersPage extends StatefulWidget {
  const PendingPreordersPage({
    super.key,
    required this.authService,
    required this.onBack,
    required this.onOpenProductsSummary,
    required this.onCharge,
    required this.onModify,
    required this.onCancel,
  });

  final AuthService authService;
  final VoidCallback onBack;
  final VoidCallback onOpenProductsSummary;
  final Future<bool> Function(TpvPreorder preorder) onCharge;
  final Future<bool> Function(TpvPreorder preorder) onModify;
  final Future<bool> Function(TpvPreorder preorder) onCancel;

  @override
  State<PendingPreordersPage> createState() => _PendingPreordersPageState();
}

class _PendingPreordersPageState extends State<PendingPreordersPage> {
  late final TpvSalesService _salesService = TpvSalesService(
    ApiClient(),
    widget.authService,
  );
  bool _loading = true;
  List<TpvPreorder> _preorders = <TpvPreorder>[];
  final Map<int, Future<TpvOrderDetail>> _detailsFutures =
      <int, Future<TpvOrderDetail>>{};

  Future<TpvOrderDetail> _detailFor(int orderId) {
    return _detailsFutures.putIfAbsent(
      orderId,
      () => _salesService.fetchOrderDetails(orderId: orderId),
    );
  }

  int _gridColumns(double width) {
    if (width >= 1500) return 4;
    if (width >= 1180) return 3;
    if (width >= 860) return 2;
    return 1;
  }

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final List<TpvPreorder> pending = await _salesService
          .fetchPendingPreorders();
      if (!mounted) return;
      setState(() => _preorders = pending);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _refreshAfterAction(int orderId) async {
    _detailsFutures.remove(orderId);
    await _load();
  }

  @override
  Widget build(BuildContext context) {
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
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.86),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: const Color(0xFFE4E8F4)),
                boxShadow: const <BoxShadow>[
                  BoxShadow(
                    color: Color(0x12000000),
                    blurRadius: 18,
                    offset: Offset(0, 7),
                  ),
                ],
              ),
              child: Column(
                children: <Widget>[
                  _buildHeader(),
                  const SizedBox(height: 14),
                  Expanded(child: _buildBody()),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      children: <Widget>[
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              const Text(
                'Encarrecs Pendents',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
              ),
              Text(
                '${_preorders.length} encarregs pendents',
                style: const TextStyle(color: TpvTheme.textSecondary),
              ),
            ],
          ),
        ),
        OutlinedButton.icon(
          onPressed: widget.onOpenProductsSummary,
          icon: const Icon(Icons.inventory_2_outlined, size: 18),
          label: const Text('Productes encarregats'),
        ),
        const SizedBox(width: 10),
        OutlinedButton.icon(
          onPressed: widget.onBack,
          icon: const Icon(Icons.chevron_left_rounded),
          label: const Text('Tornar al TPV'),
          style: OutlinedButton.styleFrom(
            minimumSize: const Size(154, 48),
            padding: const EdgeInsets.symmetric(
              horizontal: 18,
              vertical: 14,
            ),
            side: const BorderSide(color: Color(0xFFBFC8E4), width: 1.5),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(14),
            ),
            textStyle: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_preorders.isEmpty) {
      return const Center(child: Text('Cap encarrec pendent'));
    }

    return LayoutBuilder(
      builder: (BuildContext _, BoxConstraints constraints) {
        final int columns = _gridColumns(constraints.maxWidth);
        const double spacing = 12;

        // Repartim els encàrrecs en columnes independents (estil masonry):
        // així expandir una targeta només desplaça les que té a sota dins
        // de la seva pròpia columna i no afecta la resta.
        final List<List<TpvPreorder>> buckets = List<List<TpvPreorder>>.generate(
          columns,
          (_) => <TpvPreorder>[],
        );
        for (int i = 0; i < _preorders.length; i++) {
          buckets[i % columns].add(_preorders[i]);
        }

        final List<Widget> rowChildren = <Widget>[];
        for (int c = 0; c < columns; c++) {
          if (c > 0) {
            rowChildren.add(const SizedBox(width: spacing));
          }
          rowChildren.add(
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: <Widget>[
                  for (int i = 0; i < buckets[c].length; i++) ...<Widget>[
                    if (i > 0) const SizedBox(height: spacing),
                    _PreorderCard(
                      key: ValueKey<int>(buckets[c][i].id),
                      preorder: buckets[c][i],
                      detailLoader: () => _detailFor(buckets[c][i].id),
                      onCharge: () async {
                        await widget.onCharge(buckets[c][i]);
                        await _refreshAfterAction(buckets[c][i].id);
                      },
                      onModify: () async {
                        await widget.onModify(buckets[c][i]);
                        await _refreshAfterAction(buckets[c][i].id);
                      },
                      onCancel: () async {
                        await widget.onCancel(buckets[c][i]);
                        await _refreshAfterAction(buckets[c][i].id);
                      },
                    ),
                  ],
                ],
              ),
            ),
          );
        }

        return SingleChildScrollView(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: rowChildren,
          ),
        );
      },
    );
  }
}

class _PreorderCard extends StatefulWidget {
  const _PreorderCard({
    super.key,
    required this.preorder,
    required this.detailLoader,
    required this.onCharge,
    required this.onModify,
    required this.onCancel,
  });

  final TpvPreorder preorder;
  final Future<TpvOrderDetail> Function() detailLoader;
  final Future<void> Function() onCharge;
  final Future<void> Function() onModify;
  final Future<void> Function() onCancel;

  @override
  State<_PreorderCard> createState() => _PreorderCardState();
}

class _PreorderCardState extends State<_PreorderCard> {
  bool _expanded = false;
  Future<TpvOrderDetail>? _detailFuture;

  void _toggle() {
    setState(() {
      _expanded = !_expanded;
      if (_expanded) {
        _detailFuture ??= widget.detailLoader();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final TpvPreorder p = widget.preorder;
    final BorderRadius radius = BorderRadius.circular(14);

    return Material(
      color: _expanded ? const Color(0xFFF6F8FF) : Colors.white,
      borderRadius: radius,
      child: InkWell(
        onTap: _toggle,
        borderRadius: radius,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: radius,
            border: Border.all(
              color: _expanded
                  ? TpvTheme.primary.withValues(alpha: 0.45)
                  : const Color(0xFFE4E8F4),
            ),
            boxShadow: <BoxShadow>[
              BoxShadow(
                color: _expanded
                    ? TpvTheme.primary.withValues(alpha: 0.10)
                    : const Color(0x10000000),
                blurRadius: _expanded ? 14 : 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              _buildHeader(p),
              const SizedBox(height: 4),
              _buildSubtitle(p),
              const SizedBox(height: 8),
              AnimatedSize(
                duration: const Duration(milliseconds: 220),
                curve: Curves.easeInOut,
                alignment: Alignment.topCenter,
                child: _expanded
                    ? _buildDetail()
                    : _buildCollapsedHint(p),
              ),
              const SizedBox(height: 10),
              _buildActions(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(TpvPreorder p) {
    return Row(
      children: <Widget>[
        Expanded(
          child: Text(
            'Encarrec #${p.pickupNumber ?? p.id}',
            style: const TextStyle(
              fontWeight: FontWeight.w900,
              fontSize: 24,
            ),
          ),
        ),
        Text(
          '${p.totalPrice.toStringAsFixed(2)}€',
          style: const TextStyle(
            fontWeight: FontWeight.w900,
            fontSize: 20,
            color: TpvTheme.primary,
          ),
        ),
        const SizedBox(width: 6),
        AnimatedRotation(
          turns: _expanded ? 0.5 : 0,
          duration: const Duration(milliseconds: 200),
          child: const Icon(
            Icons.keyboard_arrow_down_rounded,
            color: TpvTheme.textSecondary,
            size: 24,
          ),
        ),
      ],
    );
  }

  Widget _buildSubtitle(TpvPreorder p) {
    final String dateLabel = _shortDateLabel(p.pickupDate);
    final String prefix = dateLabel.isEmpty ? '' : '$dateLabel · ';
    return Text(
      '$prefix${p.pickupTime ?? '--:--'} · ${p.customerName ?? 'Sense nom'}',
      style: const TextStyle(
        color: TpvTheme.textSecondary,
        fontWeight: FontWeight.w700,
        fontSize: 13,
      ),
    );
  }

  /// Etiqueta curta tipus "Avui", "Demà", "Ahir" o "DD/MM" per al subtítol.
  String _shortDateLabel(String? iso) {
    if (iso == null || iso.isEmpty) return '';
    final DateTime? parsed = DateTime.tryParse(iso);
    if (parsed == null) return '';
    final DateTime today = DateTime.now();
    final DateTime todayDate = DateTime(today.year, today.month, today.day);
    final DateTime targetDate = DateTime(
      parsed.year,
      parsed.month,
      parsed.day,
    );
    final int diff = targetDate.difference(todayDate).inDays;
    if (diff == 0) return 'Avui';
    if (diff == 1) return 'Demà';
    if (diff == -1) return 'Ahir';
    return '${targetDate.day.toString().padLeft(2, '0')}/'
        '${targetDate.month.toString().padLeft(2, '0')}';
  }

  Widget _buildCollapsedHint(TpvPreorder p) {
    return Container(
      key: const ValueKey<String>('collapsed'),
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFF5F7FD),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE4E8F4)),
      ),
      child: Row(
        children: <Widget>[
          const Icon(
            Icons.touch_app_outlined,
            size: 18,
            color: TpvTheme.textSecondary,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              p.itemsCount > 0
                  ? 'Toca per veure els ${p.itemsCount} productes'
                  : 'Toca per veure el detall',
              style: const TextStyle(
                color: TpvTheme.textSecondary,
                fontWeight: FontWeight.w700,
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetail() {
    return Container(
      key: const ValueKey<String>('expanded'),
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE4E8F4)),
      ),
      child: FutureBuilder<TpvOrderDetail>(
        future: _detailFuture,
        builder:
            (
              BuildContext context,
              AsyncSnapshot<TpvOrderDetail> snapshot,
            ) {
              if (snapshot.connectionState != ConnectionState.done &&
                  !snapshot.hasData) {
                return const Padding(
                  padding: EdgeInsets.symmetric(vertical: 10),
                  child: Center(
                    child: SizedBox(
                      height: 22,
                      width: 22,
                      child: CircularProgressIndicator(strokeWidth: 2.4),
                    ),
                  ),
                );
              }
              if (snapshot.hasError) {
                return const Text(
                  'No s\'ha pogut carregar el detall',
                  style: TextStyle(
                    color: TpvTheme.danger,
                    fontWeight: FontWeight.w700,
                  ),
                );
              }
              final List<TpvOrderDetailItem> items =
                  snapshot.data?.items ?? <TpvOrderDetailItem>[];
              if (items.isEmpty) {
                return const Text(
                  'Sense productes',
                  style: TextStyle(color: TpvTheme.textSecondary),
                );
              }
              final double totalLine = items.fold<double>(
                0,
                (double s, TpvOrderDetailItem it) =>
                    s + it.priceAtSale * it.quantity,
              );
              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: <Widget>[
                  for (final TpvOrderDetailItem item in items)
                    _DetailItemRow(item: item),
                  const SizedBox(height: 6),
                  const Divider(height: 12, color: Color(0xFFE4E8F4)),
                  Row(
                    children: <Widget>[
                      const Text(
                        'Total',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                          color: TpvTheme.textMain,
                        ),
                      ),
                      const Spacer(),
                      Text(
                        '${totalLine.toStringAsFixed(2)}€',
                        style: const TextStyle(
                          color: TpvTheme.primary,
                          fontWeight: FontWeight.w900,
                          fontSize: 15,
                        ),
                      ),
                    ],
                  ),
                ],
              );
            },
      ),
    );
  }

  Widget _buildActions() {
    return Row(
      children: <Widget>[
        OutlinedButton(
          onPressed: () => widget.onCancel(),
          style: OutlinedButton.styleFrom(
            minimumSize: const Size(94, 40),
            foregroundColor: TpvTheme.danger,
            side: const BorderSide(color: TpvTheme.danger, width: 1.5),
          ),
          child: const Text('Anul·lar'),
        ),
        const SizedBox(width: 8),
        OutlinedButton(
          onPressed: () => widget.onModify(),
          style: OutlinedButton.styleFrom(minimumSize: const Size(104, 40)),
          child: const Text('Modificar'),
        ),
        const Spacer(),
        FilledButton(
          onPressed: () => widget.onCharge(),
          style: FilledButton.styleFrom(minimumSize: const Size(106, 40)),
          child: const Text('Cobrar'),
        ),
      ],
    );
  }
}

class _DetailItemRow extends StatelessWidget {
  const _DetailItemRow({required this.item});

  final TpvOrderDetailItem item;

  @override
  Widget build(BuildContext context) {
    final double subtotal = item.priceAtSale * item.quantity;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Container(
            margin: const EdgeInsets.only(top: 1),
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: const Color(0xFFE8EDFF),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              '×${item.quantity}',
              style: const TextStyle(
                color: Color(0xFF4E73DF),
                fontWeight: FontWeight.w900,
                fontSize: 12,
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(
                  item.productName,
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 13.5,
                    color: TpvTheme.textMain,
                  ),
                ),
                if (item.notes != null && item.notes!.trim().isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 2),
                    child: Text(
                      item.notes!.trim(),
                      style: const TextStyle(
                        color: TpvTheme.textSecondary,
                        fontStyle: FontStyle.italic,
                        fontSize: 12,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '${subtotal.toStringAsFixed(2)}€',
            style: const TextStyle(
              color: Color(0xFF1C8B43),
              fontWeight: FontWeight.w800,
              fontSize: 13,
            ),
          ),
        ],
      ),
    );
  }
}
