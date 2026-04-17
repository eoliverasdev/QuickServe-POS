import 'package:flutter/material.dart';

import '../../../../core/network/api_client.dart';
import '../../../../core/theme/tpv_theme.dart';
import '../../../auth/data/auth_service.dart';
import '../../data/admin_service.dart';
import '../../domain/admin_models.dart';

class CaixaSection extends StatefulWidget {
  const CaixaSection({super.key, required this.authService});

  final AuthService authService;

  @override
  State<CaixaSection> createState() => _CaixaSectionState();
}

class _CaixaSectionState extends State<CaixaSection> {
  late final AdminService _service = AdminService(ApiClient(), widget.authService);
  AdminDashboardData? _data;
  Object? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final AdminDashboardData data = await _service.fetchDashboard();
      if (!mounted) return;
      setState(() => _data = data);
    } catch (err) {
      if (!mounted) return;
      setState(() => _error = err);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Icon(Icons.error_outline, size: 48, color: TpvTheme.danger),
            const SizedBox(height: 8),
            const Text('No s\'ha pogut carregar la caixa', style: TextStyle(fontWeight: FontWeight.w800)),
            const SizedBox(height: 4),
            Text('$_error', textAlign: TextAlign.center, style: const TextStyle(color: TpvTheme.textSecondary)),
            const SizedBox(height: 10),
            OutlinedButton(onPressed: _load, child: const Text('Reintentar')),
          ],
        ),
      );
    }

    final AdminDashboardData data = _data!;
    final AdminKpi kpi = data.kpi;
    final AdminCaixaSummary caixa = data.caixa;

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: <Widget>[
            const Padding(
              padding: EdgeInsets.only(bottom: 14, left: 2),
              child: Text(
                'Control total sobre els ingressos de la jornada actual.',
                style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700, fontSize: 14),
              ),
            ),
            LayoutBuilder(builder: (BuildContext _, BoxConstraints c) {
              final int cols = c.maxWidth >= 1000 ? 3 : c.maxWidth >= 620 ? 2 : 1;
              final double w = (c.maxWidth - (cols - 1) * 12) / cols;
              return Wrap(
                spacing: 12,
                runSpacing: 12,
                children: <Widget>[
                  SizedBox(
                    width: w,
                    child: _CaixaStatCard(
                      title: 'Total acumulat avui',
                      value: '${kpi.totalToday.toStringAsFixed(2)}€',
                      subtitle: 'Ingressos bruts de la jornada',
                      icon: Icons.payments_rounded,
                      accent: const Color(0xFFFFB347),
                    ),
                  ),
                  SizedBox(
                    width: w,
                    child: _CaixaStatCard(
                      title: 'Efectiu en calaix',
                      value: '${kpi.cashToday.toStringAsFixed(2)}€',
                      subtitle: 'Ha de coincidir amb la caixa forta.',
                      icon: Icons.account_balance_wallet_rounded,
                      accent: const Color(0xFF22C55E),
                    ),
                  ),
                  SizedBox(
                    width: w,
                    child: _CaixaStatCard(
                      title: 'Pagaments via targeta',
                      value: '${kpi.cardToday.toStringAsFixed(2)}€',
                      subtitle: 'Diners al datàfon / TPV virtual.',
                      icon: Icons.credit_card_rounded,
                      accent: const Color(0xFF3B82F6),
                    ),
                  ),
                ],
              );
            }),
            const SizedBox(height: 14),
            _IvaBreakdownCard(caixa: caixa),
            const SizedBox(height: 14),
          ],
        ),
      ),
    );
  }
}

class _CaixaStatCard extends StatelessWidget {
  const _CaixaStatCard({
    required this.title,
    required this.value,
    required this.subtitle,
    required this.icon,
    required this.accent,
  });

  final String title;
  final String value;
  final String subtitle;
  final IconData icon;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x0F000000), blurRadius: 14, offset: Offset(0, 5)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: accent.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: accent, size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: TpvTheme.textSecondary,
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                    letterSpacing: 0.4,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(fontWeight: FontWeight.w900, fontSize: 26, color: accent, letterSpacing: -0.5),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

class _IvaBreakdownCard extends StatelessWidget {
  const _IvaBreakdownCard({required this.caixa});
  final AdminCaixaSummary caixa;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x0F000000), blurRadius: 14, offset: Offset(0, 5)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              const Icon(Icons.receipt_long_rounded, color: TpvTheme.primary),
              const SizedBox(width: 8),
              Text(
                'Desglossament d\'IVA (${caixa.ivaPercent}%)',
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _IvaRow(
            label: 'Base imposable',
            value: '${caixa.baseImposable.toStringAsFixed(2)}€',
          ),
          _IvaRow(
            label: 'Quota IVA (${caixa.ivaPercent}%)',
            value: '${caixa.ivaQuota.toStringAsFixed(2)}€',
            valueColor: TpvTheme.danger,
          ),
          const Divider(height: 20, thickness: 1.2),
          _IvaRow(
            label: 'Total brut',
            value: '${caixa.totalBrut.toStringAsFixed(2)}€',
            bold: true,
          ),
        ],
      ),
    );
  }
}

class _IvaRow extends StatelessWidget {
  const _IvaRow({
    required this.label,
    required this.value,
    this.valueColor,
    this.bold = false,
  });

  final String label;
  final String value;
  final Color? valueColor;
  final bool bold;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: <Widget>[
          Expanded(
            child: Text(
              label,
              style: TextStyle(
                fontWeight: bold ? FontWeight.w900 : FontWeight.w700,
                fontSize: bold ? 18 : 15,
                color: TpvTheme.textMain,
              ),
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontWeight: FontWeight.w900,
              fontSize: bold ? 22 : 16,
              color: valueColor ?? TpvTheme.textMain,
            ),
          ),
        ],
      ),
    );
  }
}
