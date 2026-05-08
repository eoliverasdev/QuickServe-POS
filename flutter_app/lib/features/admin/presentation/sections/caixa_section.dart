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
  bool _closingDay = false;

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

  Future<void> _confirmAndCloseDay() async {
    if (_closingDay) return;
    final String? workerName = await _askAndVerifyPinForCloseDay();
    if (workerName == null || !mounted) return;

    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Tancar el dia'),
          content: const Text(
            'Segur que vols tancar la jornada?\n\n'
            'Es guardaran les estadístiques diàries a la base de dades i es '
            'reiniciaran els comptadors diaris de caixa.',
          ),
          actions: <Widget>[
            TextButton(
              onPressed: () => Navigator.of(context).pop(false),
              child: const Text('Cancel·lar'),
            ),
            FilledButton(
              onPressed: () => Navigator.of(context).pop(true),
              child: const Text('Sí, tancar dia'),
            ),
          ],
        );
      },
    );
    if (confirmed != true || !mounted) return;

    setState(() => _closingDay = true);
    try {
      await _service.closeDay();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Dia tancat correctament per $workerName')),
      );
      await _load();
    } catch (err) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error en tancar el dia: $err')),
      );
    } finally {
      if (mounted) setState(() => _closingDay = false);
    }
  }

  Future<String?> _askAndVerifyPinForCloseDay() async {
    String? verifiedWorker;
    const int pinLength = 4;

    await showDialog<void>(
      context: context,
      builder: (BuildContext dialogContext) {
        String currentPin = '';
        String? errorText;
        bool verifying = false;

        return StatefulBuilder(
          builder: (
            BuildContext context,
            void Function(void Function()) setModalState,
          ) {
            Widget keypadButton({
              required String label,
              required VoidCallback onTap,
              bool primary = false,
            }) {
              return SizedBox(
                height: 64,
                child: ElevatedButton(
                  onPressed: verifying ? null : onTap,
                  style: ElevatedButton.styleFrom(
                    elevation: 0,
                    backgroundColor: primary
                        ? TpvTheme.primary
                        : const Color(0xFFF6F7FC),
                    foregroundColor: primary ? Colors.white : TpvTheme.textMain,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                      side: BorderSide(
                        color: primary
                            ? TpvTheme.primary
                            : const Color(0xFFE3E6F1),
                      ),
                    ),
                  ),
                  child: Text(
                    label,
                    style: const TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              );
            }

            Future<void> submit() async {
              if (currentPin.length != pinLength) {
                setModalState(() => errorText = 'Introdueix un PIN');
                return;
              }
              setModalState(() {
                verifying = true;
                errorText = null;
              });
              try {
                final String workerName = await _service.verifyPin(currentPin);
                if (!dialogContext.mounted) return;
                verifiedWorker = workerName;
                Navigator.of(dialogContext).pop();
              } catch (err) {
                if (!dialogContext.mounted) return;
                setModalState(() {
                  currentPin = '';
                  errorText = err.toString().replaceFirst('Exception: ', '');
                });
              } finally {
                if (dialogContext.mounted) {
                  setModalState(() => verifying = false);
                }
              }
            }

            return AlertDialog(
              title: const Text('Autorització requerida'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  const Text(
                    'Introdueix el PIN d\'encarregat per tancar el dia.',
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
                          border: Border.all(
                            color: const Color(0xFFCFD4E3),
                            width: 2,
                          ),
                        ),
                      );
                    }),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: 240,
                    child: Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: <Widget>[
                        for (final String d in <String>[
                          '1',
                          '2',
                          '3',
                          '4',
                          '5',
                          '6',
                          '7',
                          '8',
                          '9',
                        ])
                          SizedBox(
                            width: (240 - 16) / 3,
                            child: keypadButton(
                              label: d,
                              onTap: () {
                                if (currentPin.length >= pinLength) return;
                                setModalState(() {
                                  errorText = null;
                                  currentPin = '$currentPin$d';
                                });
                                if (currentPin.length == pinLength) {
                                  submit();
                                }
                              },
                            ),
                          ),
                        SizedBox(
                          width: (240 - 16) / 3,
                          child: keypadButton(
                            label: 'C',
                            onTap: () => setModalState(() {
                              currentPin = '';
                              errorText = null;
                            }),
                          ),
                        ),
                        SizedBox(
                          width: (240 - 16) / 3,
                          child: keypadButton(
                            label: '0',
                            onTap: () {
                              if (currentPin.length >= pinLength) return;
                              setModalState(() {
                                errorText = null;
                                currentPin = '${currentPin}0';
                              });
                              if (currentPin.length == pinLength) {
                                submit();
                              }
                            },
                          ),
                        ),
                        SizedBox(
                          width: (240 - 16) / 3,
                          child: keypadButton(
                            label: '⌫',
                            onTap: () {
                              if (currentPin.isEmpty) return;
                              setModalState(() {
                                currentPin = currentPin.substring(
                                  0,
                                  currentPin.length - 1,
                                );
                                errorText = null;
                              });
                            },
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (errorText != null) ...<Widget>[
                    const SizedBox(height: 8),
                    Text(
                      errorText!,
                      style: const TextStyle(
                        color: TpvTheme.danger,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ],
              ),
              actions: <Widget>[
                TextButton(
                  onPressed: verifying ? null : () => Navigator.of(dialogContext).pop(),
                  child: const Text('Cancel·lar'),
                ),
                FilledButton(
                  onPressed: verifying || currentPin.length != pinLength
                      ? null
                      : submit,
                  child: verifying
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Validar PIN'),
                ),
              ],
            );
          },
        );
      },
    );

    return verifiedWorker;
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
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: _closingDay ? null : _confirmAndCloseDay,
                style: FilledButton.styleFrom(
                  backgroundColor: TpvTheme.danger,
                  minimumSize: const Size.fromHeight(52),
                ),
                icon: _closingDay
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Icon(Icons.lock_clock_rounded),
                label: Text(_closingDay ? 'Tancant dia...' : 'Tancar el dia'),
              ),
            ),
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
