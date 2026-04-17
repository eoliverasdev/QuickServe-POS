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
  late final TpvSalesService _salesService = TpvSalesService(ApiClient(), widget.authService);
  bool _loading = true;
  List<TpvPreorder> _preorders = <TpvPreorder>[];
  final Map<int, Future<TpvOrderDetail>> _detailsFutures = <int, Future<TpvOrderDetail>>{};

  Future<TpvOrderDetail> _detailFor(int orderId) {
    return _detailsFutures.putIfAbsent(orderId, () => _salesService.fetchOrderDetails(orderId: orderId));
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
      final List<TpvPreorder> pending = await _salesService.fetchPendingPreorders();
      if (!mounted) return;
      setState(() => _preorders = pending);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final double width = MediaQuery.of(context).size.width;
    final int columns = _gridColumns(width);

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
                  BoxShadow(color: Color(0x12000000), blurRadius: 18, offset: Offset(0, 7)),
                ],
              ),
              child: Column(
                children: <Widget>[
                  Row(
                    children: <Widget>[
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: <Widget>[
                            const Text('Encarrecs Pendents', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
                            Text('${_preorders.length} encarregs pendents', style: const TextStyle(color: TpvTheme.textSecondary)),
                          ],
                        ),
                      ),
                      OutlinedButton.icon(
                        onPressed: widget.onOpenProductsSummary,
                        icon: const Icon(Icons.inventory_2_outlined, size: 18),
                        label: const Text('Productes encarregats'),
                      ),
                      const SizedBox(width: 10),
                      TextButton.icon(
                        onPressed: widget.onBack,
                        icon: const Icon(Icons.chevron_left_rounded),
                        label: const Text('Tornar al TPV'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Expanded(
                    child: _loading
                        ? const Center(child: CircularProgressIndicator())
                        : _preorders.isEmpty
                            ? const Center(child: Text('Cap encarrec pendent'))
                            : GridView.builder(
                                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: columns,
                                  crossAxisSpacing: 12,
                                  mainAxisSpacing: 12,
                                  childAspectRatio: columns == 1 ? 3.0 : 2.15,
                                ),
                                itemCount: _preorders.length,
                                itemBuilder: (_, int i) => _buildPreorderCard(_preorders[i]),
                              ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPreorderCard(TpvPreorder p) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x10000000), blurRadius: 10, offset: Offset(0, 3)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              Expanded(
                child: Text(
                  'Encarrec #${p.pickupNumber ?? p.id}',
                  style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 24),
                ),
              ),
              Text(
                '${p.totalPrice.toStringAsFixed(2)}€',
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 20, color: TpvTheme.primary),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            '${p.pickupTime ?? '--:--'} · ${p.customerName ?? 'Sense nom'}',
            style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700, fontSize: 13),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFF5F7FD),
                borderRadius: BorderRadius.circular(10),
              ),
              child: FutureBuilder<TpvOrderDetail>(
                future: _detailFor(p.id),
                builder: (BuildContext context, AsyncSnapshot<TpvOrderDetail> snapshot) {
                  if (!snapshot.hasData) {
                    return const Align(
                      alignment: Alignment.centerLeft,
                      child: Text('Carregant detall...', style: TextStyle(color: TpvTheme.textSecondary)),
                    );
                  }
                  final List<TpvOrderDetailItem> items = snapshot.data!.items;
                  if (items.isEmpty) {
                    return const Text('Sense productes', style: TextStyle(color: TpvTheme.textSecondary));
                  }
                  return ListView.separated(
                    itemCount: items.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 4),
                    itemBuilder: (_, int idx) {
                      final TpvOrderDetailItem item = items[idx];
                      return Text(
                        '${item.quantity}x ${item.productName}',
                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      );
                    },
                  );
                },
              ),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: <Widget>[
              OutlinedButton(
                onPressed: () async {
                  await widget.onCancel(p);
                  _detailsFutures.remove(p.id);
                  await _load();
                },
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(94, 40),
                  foregroundColor: TpvTheme.danger,
                  side: const BorderSide(color: TpvTheme.danger, width: 1.5),
                ),
                child: const Text('Anul·lar'),
              ),
              const SizedBox(width: 8),
              OutlinedButton(
                onPressed: () async {
                  await widget.onModify(p);
                  _detailsFutures.remove(p.id);
                  await _load();
                },
                style: OutlinedButton.styleFrom(minimumSize: const Size(104, 40)),
                child: const Text('Modificar'),
              ),
              const Spacer(),
              FilledButton(
                onPressed: () async {
                  await widget.onCharge(p);
                  _detailsFutures.remove(p.id);
                  await _load();
                },
                style: FilledButton.styleFrom(minimumSize: const Size(106, 40)),
                child: const Text('Cobrar'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
