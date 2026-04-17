import 'package:flutter/material.dart';

import '../../../core/theme/tpv_theme.dart';
import '../domain/tpv_models.dart';

class PaymentPage extends StatefulWidget {
  const PaymentPage({
    super.key,
    this.title = 'Pagament',
    this.subtitle,
    this.confirmLabel = '✅ Confirmar i tancar venda',
    this.showDiscount = true,
    required this.workerName,
    required this.cartItems,
    required this.initialTotal,
    required this.bagUnitPrice,
    required this.bagMaxCount,
    required this.onConfirm,
  });

  final String title;
  final String? subtitle;
  final String confirmLabel;
  final bool showDiscount;
  final String workerName;
  final List<CartItem> cartItems;
  final double initialTotal;
  final double bagUnitPrice;
  final int bagMaxCount;

  final Future<bool> Function({
    required String paymentMethod,
    required int bagCount,
    required bool discount,
    required double finalTotal,
    double? cashGiven,
  }) onConfirm;

  @override
  State<PaymentPage> createState() => _PaymentPageState();
}

class _PaymentPageState extends State<PaymentPage> {
  String? _paymentMethod;
  int _bagCount = 0;
  bool _discount = false;
  final TextEditingController _cashController = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _cashController.dispose();
    super.dispose();
  }

  double get _productsTotal => _discount ? widget.initialTotal * 0.85 : widget.initialTotal;
  double get _finalTotal => _productsTotal + (_bagCount * widget.bagUnitPrice);
  double get _cashGiven => double.tryParse(_cashController.text.trim().replaceAll(',', '.')) ?? 0;
  bool get _insufficientCash => _paymentMethod == 'Efectiu' && _cashGiven > 0 && _cashGiven < _finalTotal;
  bool get _canConfirm => _paymentMethod != null && !_insufficientCash && !_submitting;
  double get _changeToReturn {
    if (_paymentMethod != 'Efectiu') return 0;
    final double diff = _cashGiven - _finalTotal;
    return diff > 0 ? diff : 0;
  }

  List<double> get _quickCashOptions {
    final double total = _finalTotal;
    final int round5 = ((total / 5).ceil()) * 5;
    final int round10 = ((total / 10).ceil()) * 10;
    final Set<double> values = <double>{5, 10, 15, 20, 25, 30, 40, 50, 100, total, round5.toDouble(), round10.toDouble()};
    final List<double> sorted = values.where((double v) => v > 0).toList()..sort((a, b) => a.compareTo(b));
    return sorted;
  }

  String _amountLabel(double amount) {
    if ((amount - _finalTotal).abs() < 0.01) return 'Exacte';
    if (amount % 1 == 0) return '${amount.toInt()}€';
    return '${amount.toStringAsFixed(2).replaceAll('.', ',')}€';
  }

  Future<void> _confirm() async {
    if (!_canConfirm) return;
    setState(() => _submitting = true);
    try {
      final bool ok = await widget.onConfirm(
        paymentMethod: _paymentMethod!,
        bagCount: _bagCount,
        discount: _discount,
        finalTotal: _finalTotal,
        cashGiven: _paymentMethod == 'Efectiu' ? _cashGiven : null,
      );
      if (!mounted || !ok) return;
      Navigator.of(context).pop(true);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bool wide = MediaQuery.of(context).size.width >= 1100;
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
              padding: const EdgeInsets.all(18),
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
                  _TopBar(
                    title: widget.title,
                    subtitle: widget.subtitle ?? 'Treballador: ${widget.workerName}',
                    onBack: () => Navigator.of(context).pop(),
                  ),
                  const SizedBox(height: 14),
                  Expanded(child: wide ? _buildWideLayout() : _buildNarrowLayout()),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: _canConfirm ? _confirm : null,
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(58),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      ),
                      child: Text(_submitting ? 'Processant...' : widget.confirmLabel),
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

  Widget _panelShell({required Widget child}) {
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
      child: child,
    );
  }

  Widget _buildWideLayout() {
    return Row(
      children: <Widget>[
        Expanded(child: _buildSummaryPanel()),
        const SizedBox(width: 14),
        SizedBox(width: 430, child: _buildPaymentPanel()),
      ],
    );
  }

  Widget _buildNarrowLayout() {
    return Column(
      children: <Widget>[
        Expanded(child: _buildSummaryPanel()),
        const SizedBox(height: 10),
        SizedBox(height: 330, child: _buildPaymentPanel()),
      ],
    );
  }

  Widget _buildSummaryPanel() {
    return _panelShell(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          const Text('Resum', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
          const SizedBox(height: 10),
          Expanded(
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF4F7FF),
                borderRadius: BorderRadius.circular(14),
              ),
              child: ListView.separated(
                itemCount: widget.cartItems.length + (_bagCount > 0 ? 1 : 0),
                separatorBuilder: (_, _) => const SizedBox(height: 6),
                itemBuilder: (_, int i) {
                  final bool isBagRow = _bagCount > 0 && i == widget.cartItems.length;
                  if (isBagRow) {
                    final double bagTotal = widget.bagUnitPrice * _bagCount;
                    return Row(
                      children: <Widget>[
                        const Expanded(
                          child: Text(
                            'Bossa',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(fontWeight: FontWeight.w700),
                          ),
                        ),
                        Text('${bagTotal.toStringAsFixed(2)}€', style: const TextStyle(fontWeight: FontWeight.w900)),
                      ],
                    );
                  }

                  final CartItem item = widget.cartItems[i];
                  return Row(
                    children: <Widget>[
                      Expanded(
                        child: Text(
                          '${item.quantity}x ${item.product.name}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                      Text('${item.lineTotal.toStringAsFixed(2)}€', style: const TextStyle(fontWeight: FontWeight.w900)),
                    ],
                  );
                },
              ),
            ),
          ),
          const SizedBox(height: 12),
          _TwoCol(label: 'Base imposable', value: (_productsTotal / 1.21).toStringAsFixed(2)),
          _TwoCol(label: 'IVA (21%)', value: (_productsTotal - (_productsTotal / 1.21)).toStringAsFixed(2)),
          const SizedBox(height: 10),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: <Widget>[
              OutlinedButton.icon(
                onPressed: _bagCount >= widget.bagMaxCount ? null : () => setState(() => _bagCount++),
                icon: const Icon(Icons.shopping_bag_outlined, size: 18),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(140, 46),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                ),
                label: Text(widget.bagUnitPrice > 0
                    ? 'Bossa (+${widget.bagUnitPrice.toStringAsFixed(2)}€)${_bagCount > 0 ? ' ×$_bagCount' : ''}'
                    : 'Bossa${_bagCount > 0 ? ' ×$_bagCount' : ''}'),
              ),
              if (_bagCount > 0)
                OutlinedButton(
                  onPressed: () => setState(() => _bagCount--),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: TpvTheme.danger,
                    minimumSize: const Size(96, 46),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                  child: const Text('Treure'),
                ),
              if (widget.showDiscount)
                OutlinedButton(
                  onPressed: () => setState(() => _discount = !_discount),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: _discount ? Colors.white : Colors.green.shade800,
                    side: BorderSide(color: Colors.green.shade800),
                    backgroundColor: _discount ? Colors.green.shade700 : Colors.white,
                    minimumSize: const Size(150, 46),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                  child: const Text('-15% Treballador'),
                ),
            ],
          ),
          const SizedBox(height: 10),
          Align(
            alignment: Alignment.centerRight,
            child: Text(
              'Total: ${_finalTotal.toStringAsFixed(2)}€',
              style: const TextStyle(fontSize: 34, fontWeight: FontWeight.w900, color: TpvTheme.primary),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentPanel() {
    return _panelShell(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          const Text('Mètode de pagament', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18)),
          const SizedBox(height: 12),
          Row(
            children: <Widget>[
              Expanded(child: _MethodCard(label: '💶 Efectiu', selected: _paymentMethod == 'Efectiu', onTap: () => setState(() => _paymentMethod = 'Efectiu'))),
              const SizedBox(width: 12),
              Expanded(child: _MethodCard(label: '💳 Targeta', selected: _paymentMethod == 'Targeta', onTap: () => setState(() => _paymentMethod = 'Targeta'))),
            ],
          ),
          const SizedBox(height: 14),
          if (_paymentMethod == 'Efectiu') ...<Widget>[
            TextField(
              controller: _cashController,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              decoration: const InputDecoration(hintText: 'Import entregat', suffixText: '€'),
              onChanged: (_) => setState(() {}),
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 10,
              runSpacing: 10,
              children: <Widget>[
                for (final double amount in _quickCashOptions)
                  OutlinedButton(
                    style: OutlinedButton.styleFrom(
                      minimumSize: const Size(88, 44),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    ),
                    onPressed: () => setState(() => _cashController.text = amount.toStringAsFixed(2).replaceAll('.', ',')),
                    child: Text(_amountLabel(amount)),
                  ),
              ],
            ),
          ],
          const Spacer(),
          _buildStatusPanel(),
        ],
      ),
    );
  }

  Widget _buildStatusPanel() {
    if (_paymentMethod == 'Efectiu') {
      final bool hasCash = _cashGiven > 0;
      final bool showChange = hasCash && !_insufficientCash;

      final Color bg = _insufficientCash
          ? const Color(0xFFFFECEC)
          : showChange
              ? const Color(0xFFE8F7EE)
              : const Color(0xFFF4F7FF);
      final Color border = _insufficientCash
          ? const Color(0xFFF3B6B6)
          : showChange
              ? const Color(0xFFB7E3C5)
              : const Color(0xFFE1E6F5);
      final Color accent = _insufficientCash
          ? TpvTheme.danger
          : showChange
              ? const Color(0xFF1C8B43)
              : TpvTheme.textSecondary;

      return Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            Text(
              _insufficientCash
                  ? 'Import insuficient'
                  : 'Canvi a retornar',
              style: TextStyle(color: accent, fontWeight: FontWeight.w800, fontSize: 14, letterSpacing: 0.2),
            ),
            const SizedBox(height: 6),
            Text(
              _insufficientCash
                  ? 'Falten ${(_finalTotal - _cashGiven).toStringAsFixed(2).replaceAll('.', ',')}€'
                  : '${_changeToReturn.toStringAsFixed(2).replaceAll('.', ',')}€',
              style: TextStyle(color: accent, fontWeight: FontWeight.w900, fontSize: 34, height: 1.1),
            ),
            if (!_insufficientCash && hasCash) ...<Widget>[
              const SizedBox(height: 4),
              Text(
                'Entregat ${_cashGiven.toStringAsFixed(2).replaceAll('.', ',')}€ · Total ${_finalTotal.toStringAsFixed(2).replaceAll('.', ',')}€',
                style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 12),
              ),
            ],
          ],
        ),
      );
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF4F7FF),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        _paymentMethod == null
            ? 'Selecciona el mètode per confirmar.'
            : 'Llest per confirmar.',
        style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700),
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  const _TopBar({required this.title, required this.subtitle, required this.onBack});
  final String title;
  final String subtitle;
  final VoidCallback onBack;
  @override
  Widget build(BuildContext context) {
    return Row(
      children: <Widget>[
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: <Widget>[Text(title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)), Text(subtitle)]),
        ),
        TextButton.icon(
          onPressed: onBack,
          style: TextButton.styleFrom(
            minimumSize: const Size(132, 48),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          ),
          icon: const Icon(Icons.chevron_left_rounded, size: 22),
          label: const Text('Tornar', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
        ),
      ],
    );
  }
}

class _MethodCard extends StatelessWidget {
  const _MethodCard({required this.label, required this.selected, required this.onTap});
  final String label;
  final bool selected;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        height: 76,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: selected ? TpvTheme.primary : const Color(0xFFE0E3EE), width: 2),
          color: selected ? const Color(0xFFF0F3FF) : Colors.white,
        ),
        child: Center(child: Text(label)),
      ),
    );
  }
}

class _TwoCol extends StatelessWidget {
  const _TwoCol({required this.label, required this.value});
  final String label;
  final String value;
  @override
  Widget build(BuildContext context) {
    return Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: <Widget>[Text(label), Text('$value€')]);
  }
}

