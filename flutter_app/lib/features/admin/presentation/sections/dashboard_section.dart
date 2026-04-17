import 'dart:math' as math;

import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';

import '../../../../core/network/api_client.dart';
import '../../../../core/theme/tpv_theme.dart';
import '../../../auth/data/auth_service.dart';
import '../../data/admin_service.dart';
import '../../domain/admin_models.dart';

class DashboardSection extends StatefulWidget {
  const DashboardSection({super.key, required this.authService});

  final AuthService authService;

  @override
  State<DashboardSection> createState() => _DashboardSectionState();
}

class _DashboardSectionState extends State<DashboardSection> {
  late final AdminService _service = AdminService(ApiClient(), widget.authService);
  AdminDashboardData? _data;
  Object? _error;
  bool _loading = true;
  int _selectedDow = DateTime.now().weekday % 7; // 0=Sunday

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
      setState(() {
        _data = data;
        _selectedDow = data.currentDow;
      });
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
            const Text('No s\'ha pogut carregar el panell', style: TextStyle(fontWeight: FontWeight.w800)),
            const SizedBox(height: 4),
            Text('$_error', textAlign: TextAlign.center, style: const TextStyle(color: TpvTheme.textSecondary)),
            const SizedBox(height: 10),
            OutlinedButton(onPressed: _load, child: const Text('Reintentar')),
          ],
        ),
      );
    }

    final AdminDashboardData data = _data!;
    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: <Widget>[
            _buildKpiGrid(data.kpi),
            const SizedBox(height: 14),
            _buildTwoCols(
              left: _RevenueChartCard(days: data.revenueWeek),
              right: _TopProductsCard(items: data.topProducts),
            ),
            const SizedBox(height: 14),
            _TopPerDayCard(
              days: data.topPerDay,
              currentDow: data.currentDow,
              selectedDow: _selectedDow,
              onSelectDow: (int dow) => setState(() => _selectedDow = dow),
            ),
            const SizedBox(height: 14),
            _buildTwoCols(
              left: _PeakHoursCard(hours: data.peakHours),
              right: _PaymentDonutCard(split: data.paymentMonth),
            ),
            const SizedBox(height: 14),
          ],
        ),
      ),
    );
  }

  Widget _buildKpiGrid(AdminKpi kpi) {
    final double width = MediaQuery.of(context).size.width;
    final int cols = width >= 1200
        ? 4
        : width >= 820
            ? 3
            : width >= 540
                ? 2
                : 1;

    final List<Widget> cards = <Widget>[
      _KpiCard(
        title: 'Total Avui',
        value: '${kpi.totalToday.toStringAsFixed(2)}€',
        subtitle: 'últims 30 dies: ${kpi.totalLast30d.toStringAsFixed(2)}€',
        icon: Icons.payments_rounded,
        accent: const Color(0xFFFFB347),
      ),
      _KpiCard(
        title: 'Comandes Avui',
        value: '${kpi.ordersToday}',
        subtitle: 'Tiquet mig: ${kpi.ticketAvg.toStringAsFixed(2)}€',
        icon: Icons.receipt_long_rounded,
        accent: const Color(0xFF22C55E),
      ),
      _KpiCard(
        title: 'Efectiu / Targeta',
        value: '${kpi.cashToday.toStringAsFixed(2)}€ · ${kpi.cardToday.toStringAsFixed(2)}€',
        subtitle: 'Avui',
        icon: Icons.credit_card_rounded,
        accent: const Color(0xFF3B82F6),
      ),
      _KpiCard(
        title: 'Millor Treballador',
        value: kpi.bestWorker ?? '—',
        subtitle: 'per comandes avui',
        icon: Icons.emoji_events_rounded,
        accent: const Color(0xFFB4789C),
      ),
    ];

    return LayoutBuilder(
      builder: (BuildContext _, BoxConstraints constraints) {
        final double itemWidth = (constraints.maxWidth - (cols - 1) * 12) / cols;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: cards.map((Widget c) => SizedBox(width: itemWidth, child: c)).toList(),
        );
      },
    );
  }

  Widget _buildTwoCols({required Widget left, required Widget right}) {
    return LayoutBuilder(builder: (BuildContext _, BoxConstraints c) {
      if (c.maxWidth < 820) {
        return Column(children: <Widget>[left, const SizedBox(height: 12), right]);
      }
      return Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Expanded(child: left),
          const SizedBox(width: 12),
          Expanded(child: right),
        ],
      );
    });
  }
}

// ── Shell cards & primitives ─────────────────────────────────────────────

class _ShellCard extends StatelessWidget {
  const _ShellCard({required this.child, this.padding = const EdgeInsets.all(16)});
  final Widget child;
  final EdgeInsets padding;
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x0F000000), blurRadius: 14, offset: Offset(0, 5)),
        ],
      ),
      child: child,
    );
  }
}

class _KpiCard extends StatelessWidget {
  const _KpiCard({
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
    return _ShellCard(
      padding: const EdgeInsets.all(16),
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
            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 24, letterSpacing: -0.5),
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

// ── Revenue (7 days) bar chart ───────────────────────────────────────────

class _RevenueChartCard extends StatelessWidget {
  const _RevenueChartCard({required this.days});
  final List<AdminRevenueDay> days;

  @override
  Widget build(BuildContext context) {
    final double maxVal = days.fold<double>(0, (double m, AdminRevenueDay d) => math.max(m, d.total));
    final double topY = maxVal <= 0 ? 10 : (maxVal * 1.25);

    return _ShellCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          _CardTitle(icon: Icons.show_chart_rounded, label: 'Ingressos · Últims 7 dies'),
          const SizedBox(height: 12),
          SizedBox(
            height: 220,
            child: BarChart(
              BarChartData(
                maxY: topY,
                minY: 0,
                alignment: BarChartAlignment.spaceAround,
                gridData: FlGridData(
                  show: true,
                  drawVerticalLine: false,
                  getDrawingHorizontalLine: (_) => const FlLine(
                    color: Color(0xFFEEF1F9),
                    strokeWidth: 1,
                  ),
                ),
                titlesData: FlTitlesData(
                  topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  leftTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 40,
                      getTitlesWidget: (double value, TitleMeta meta) {
                        if (value == 0) return const SizedBox.shrink();
                        return Padding(
                          padding: const EdgeInsets.only(right: 6),
                          child: Text(
                            '${value.toInt()}€',
                            style: const TextStyle(
                              color: TpvTheme.textSecondary,
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 28,
                      getTitlesWidget: (double value, TitleMeta meta) {
                        final int i = value.toInt();
                        if (i < 0 || i >= days.length) return const SizedBox.shrink();
                        return Padding(
                          padding: const EdgeInsets.only(top: 6),
                          child: Text(
                            days[i].label,
                            style: const TextStyle(
                              color: TpvTheme.textSecondary,
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ),
                borderData: FlBorderData(show: false),
                barTouchData: BarTouchData(
                  touchTooltipData: BarTouchTooltipData(
                    getTooltipColor: (_) => TpvTheme.primary,
                    tooltipBorderRadius: BorderRadius.circular(10),
                    getTooltipItem: (BarChartGroupData group, int _, BarChartRodData rod, int __) {
                      final int i = group.x;
                      if (i < 0 || i >= days.length) return null;
                      return BarTooltipItem(
                        '${days[i].label}\n${days[i].total.toStringAsFixed(2)}€',
                        const TextStyle(color: Colors.white, fontWeight: FontWeight.w800),
                      );
                    },
                  ),
                ),
                barGroups: <BarChartGroupData>[
                  for (int i = 0; i < days.length; i++)
                    BarChartGroupData(
                      x: i,
                      barRods: <BarChartRodData>[
                        BarChartRodData(
                          toY: days[i].total,
                          width: 18,
                          borderRadius: const BorderRadius.vertical(top: Radius.circular(8)),
                          gradient: const LinearGradient(
                            begin: Alignment.bottomCenter,
                            end: Alignment.topCenter,
                            colors: <Color>[Color(0xFF5D7FE7), TpvTheme.primary],
                          ),
                        ),
                      ],
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Top products (global) ────────────────────────────────────────────────

class _TopProductsCard extends StatelessWidget {
  const _TopProductsCard({required this.items});
  final List<AdminTopProduct> items;

  @override
  Widget build(BuildContext context) {
    final double maxVal = items.isEmpty ? 1 : items.first.totalSold;
    return _ShellCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          _CardTitle(icon: Icons.local_fire_department_rounded, label: 'Productes més venuts'),
          const SizedBox(height: 8),
          if (items.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 18),
              child: Center(
                child: Text(
                  'Encara no hi ha dades de vendes.',
                  style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700),
                ),
              ),
            )
          else
            ...items.take(6).toList().asMap().entries.map((MapEntry<int, AdminTopProduct> e) {
              final int i = e.key;
              final AdminTopProduct p = e.value;
              final double pct = maxVal == 0 ? 0 : (p.totalSold / maxVal).clamp(0, 1);
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: <Widget>[
                    Row(
                      children: <Widget>[
                        SizedBox(width: 26, child: Text(_rankEmoji(i), style: const TextStyle(fontSize: 16))),
                        Expanded(
                          child: Text(
                            p.name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontWeight: FontWeight.w800),
                          ),
                        ),
                        Text(
                          '${p.totalSold.toStringAsFixed(1)} un.',
                          style: const TextStyle(color: TpvTheme.primary, fontWeight: FontWeight.w800, fontSize: 13),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Container(
                      height: 8,
                      decoration: BoxDecoration(
                        color: const Color(0xFFEDF0FA),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: FractionallySizedBox(
                        alignment: Alignment.centerLeft,
                        widthFactor: pct,
                        child: Container(
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: <Color>[Color(0xFF5D7FE7), TpvTheme.primary],
                            ),
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }
}

String _rankEmoji(int i) {
  switch (i) {
    case 0:
      return '🥇';
    case 1:
      return '🥈';
    case 2:
      return '🥉';
    default:
      return '#${i + 1}';
  }
}

// ── Top per day of week ──────────────────────────────────────────────────

class _TopPerDayCard extends StatelessWidget {
  const _TopPerDayCard({
    required this.days,
    required this.currentDow,
    required this.selectedDow,
    required this.onSelectDow,
  });

  final List<AdminDayTop> days;
  final int currentDow;
  final int selectedDow;
  final ValueChanged<int> onSelectDow;

  @override
  Widget build(BuildContext context) {
    final AdminDayTop? selected = days.isEmpty
        ? null
        : days.firstWhere(
            (AdminDayTop d) => d.dow == selectedDow,
            orElse: () => days.first,
          );

    return _ShellCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          _CardTitle(icon: Icons.calendar_month_rounded, label: 'Top productes per dia'),
          const SizedBox(height: 10),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: days.map((AdminDayTop d) {
                final bool active = d.dow == selectedDow;
                final bool today = d.dow == currentDow;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: InkWell(
                    onTap: () => onSelectDow(d.dow),
                    borderRadius: BorderRadius.circular(14),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        gradient: active
                            ? const LinearGradient(
                                colors: <Color>[Color(0xFF5D7FE7), TpvTheme.primary],
                              )
                            : null,
                        color: active ? null : Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: active ? Colors.transparent : const Color(0xFFE0E6F3)),
                      ),
                      child: Text(
                        '${d.shortName}${today ? ' ★' : ''}',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          color: active ? Colors.white : TpvTheme.textMain,
                        ),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 12),
          if (selected == null || selected.items.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 14),
              child: Text(
                'Sense dades per a aquest dia.',
                style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700),
              ),
            )
          else
            Wrap(
              spacing: 10,
              runSpacing: 10,
              children: selected.items.asMap().entries.map((MapEntry<int, AdminTopProduct> e) {
                final int i = e.key;
                final AdminTopProduct p = e.value;
                return Container(
                  padding: const EdgeInsets.all(12),
                  constraints: const BoxConstraints(minWidth: 150, maxWidth: 220),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF7F9FE),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFE4E8F4)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Text(
                        i < 3 ? _rankEmoji(i) : '🍽',
                        style: const TextStyle(fontSize: 18),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        p.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${p.totalSold.toStringAsFixed(1)} venuts',
                        style: const TextStyle(color: TpvTheme.primary, fontWeight: FontWeight.w800, fontSize: 12),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
        ],
      ),
    );
  }
}

// ── Peak hours heatmap ───────────────────────────────────────────────────

class _PeakHoursCard extends StatelessWidget {
  const _PeakHoursCard({required this.hours});
  final List<AdminPeakHour> hours;

  @override
  Widget build(BuildContext context) {
    final int maxCount = hours.fold<int>(0, (int m, AdminPeakHour h) => math.max(m, h.count));
    return _ShellCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          _CardTitle(icon: Icons.schedule_rounded, label: 'Hores punta · últims 30 dies'),
          const SizedBox(height: 12),
          LayoutBuilder(builder: (BuildContext _, BoxConstraints c) {
            final int cols = math.max(4, (c.maxWidth / 60).floor());
            return Wrap(
              spacing: 6,
              runSpacing: 6,
              children: hours.map((AdminPeakHour h) {
                final double pct = maxCount == 0 ? 0 : h.count / maxCount;
                final double alpha = 0.08 + (pct * 0.92);
                final Color bg = TpvTheme.primary.withValues(alpha: alpha);
                final Color fg = pct > 0.45 ? Colors.white : TpvTheme.primary;
                return SizedBox(
                  width: (c.maxWidth - (cols - 1) * 6) / cols,
                  height: 64,
                  child: Container(
                    decoration: BoxDecoration(
                      color: bg,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    alignment: Alignment.center,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: <Widget>[
                        Text(
                          h.hour.toString().padLeft(2, '0'),
                          style: TextStyle(fontWeight: FontWeight.w900, color: fg, fontSize: 14),
                        ),
                        Text(
                          '${h.count}',
                          style: TextStyle(fontWeight: FontWeight.w700, color: fg, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            );
          }),
          const SizedBox(height: 8),
          const Align(
            alignment: Alignment.center,
            child: Text(
              'Distribució de vendes per hora',
              style: TextStyle(color: TpvTheme.textSecondary, fontSize: 11, fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Payment donut ────────────────────────────────────────────────────────

class _PaymentDonutCard extends StatelessWidget {
  const _PaymentDonutCard({required this.split});
  final AdminPaymentSplit split;

  @override
  Widget build(BuildContext context) {
    final double total = split.total;
    final bool hasData = total > 0;

    return _ShellCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          _CardTitle(icon: Icons.credit_card_rounded, label: 'Mètode pagament · 30 dies'),
          const SizedBox(height: 12),
          Row(
            children: <Widget>[
              SizedBox(
                width: 140,
                height: 140,
                child: hasData
                    ? PieChart(
                        PieChartData(
                          sectionsSpace: 2,
                          centerSpaceRadius: 42,
                          sections: <PieChartSectionData>[
                            PieChartSectionData(
                              value: split.cash,
                              color: const Color(0xFF22C55E),
                              radius: 22,
                              showTitle: false,
                            ),
                            PieChartSectionData(
                              value: split.card,
                              color: const Color(0xFF3B82F6),
                              radius: 22,
                              showTitle: false,
                            ),
                          ],
                        ),
                      )
                    : Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFFF2F4FB),
                          shape: BoxShape.circle,
                        ),
                        alignment: Alignment.center,
                        child: const Text(
                          'Sense dades',
                          style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w800),
                        ),
                      ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: <Widget>[
                    _PaymentPill(
                      color: const Color(0xFF22C55E),
                      label: 'Efectiu',
                      amount: split.cash,
                    ),
                    const SizedBox(height: 10),
                    _PaymentPill(
                      color: const Color(0xFF3B82F6),
                      label: 'Targeta',
                      amount: split.card,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _PaymentPill extends StatelessWidget {
  const _PaymentPill({required this.color, required this.label, required this.amount});
  final Color color;
  final String label;
  final double amount;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.25)),
      ),
      child: Row(
        children: <Widget>[
          Container(
            width: 10,
            height: 10,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              label,
              style: const TextStyle(fontWeight: FontWeight.w800, color: TpvTheme.textMain),
            ),
          ),
          Text(
            '${amount.toStringAsFixed(2)}€',
            style: TextStyle(fontWeight: FontWeight.w900, color: color, fontSize: 16),
          ),
        ],
      ),
    );
  }
}

class _CardTitle extends StatelessWidget {
  const _CardTitle({required this.icon, required this.label});
  final IconData icon;
  final String label;
  @override
  Widget build(BuildContext context) {
    return Row(
      children: <Widget>[
        Icon(icon, size: 18, color: TpvTheme.primary),
        const SizedBox(width: 8),
        Text(
          label,
          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: TpvTheme.textMain),
        ),
      ],
    );
  }
}
