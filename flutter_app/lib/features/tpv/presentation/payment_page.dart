import 'package:flutter/material.dart';

import '../../../core/theme/tpv_theme.dart';
import '../domain/tpv_models.dart';
import 'tpv_responsive.dart';

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
  })
  onConfirm;

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

  double get _productsTotal =>
      _discount ? widget.initialTotal * 0.85 : widget.initialTotal;
  double get _finalTotal => _productsTotal + (_bagCount * widget.bagUnitPrice);
  double get _cashGiven =>
      double.tryParse(_cashController.text.trim().replaceAll(',', '.')) ?? 0;
  bool get _insufficientCash =>
      _paymentMethod == 'Efectiu' && _cashGiven > 0 && _cashGiven < _finalTotal;
  bool get _canConfirm =>
      _paymentMethod != null && !_insufficientCash && !_submitting;
  double get _changeToReturn {
    if (_paymentMethod != 'Efectiu') return 0;
    final double diff = _cashGiven - _finalTotal;
    return diff > 0 ? diff : 0;
  }

  String _amountLabel(double amount) {
    if ((amount - _finalTotal).abs() < 0.01) return 'Exacte';
    if (amount % 1 == 0) return '${amount.toInt()}€';
    return '${amount.toStringAsFixed(2).replaceAll('.', ',')}€';
  }

  String get _cashDisplay {
    final String text = _cashController.text.trim();
    return text.isEmpty ? '0,00€' : '${text.replaceAll('.', ',')}€';
  }

  void _setCashText(String value) {
    _cashController.text = value;
    _cashController.selection = TextSelection.collapsed(
      offset: _cashController.text.length,
    );
  }

  void _setCashAmount(double amount) {
    setState(
      () => _setCashText(amount.toStringAsFixed(2).replaceAll('.', ',')),
    );
  }

  void _appendCashKey(String key) {
    setState(() {
      String text = _cashController.text.trim().replaceAll('.', ',');
      if (key == ',') {
        if (text.contains(',')) return;
        _setCashText(text.isEmpty ? '0,' : '$text,');
        return;
      }

      final int comma = text.indexOf(',');
      if (comma >= 0 && text.length - comma > 2) return;
      if (text == '0') text = '';
      _setCashText('$text$key');
    });
  }

  void _deleteCashDigit() {
    setState(() {
      final String text = _cashController.text.trim();
      if (text.isEmpty) return;
      _setCashText(text.substring(0, text.length - 1));
    });
  }

  void _clearCash() {
    setState(() => _cashController.clear());
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
    final double w = MediaQuery.sizeOf(context).width;
    final bool wide = w >= 900;
    final double outerPad = TpvResponsive.screenEdgePadding(w);
    final double shellPad = (w < 800 ? 12.0 : 18.0).clamp(12.0, 20.0);

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
            padding: EdgeInsets.all(outerPad),
            child: Container(
              padding: EdgeInsets.all(shellPad),
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
                  _TopBar(
                    title: widget.title,
                    subtitle:
                        widget.subtitle ?? 'Treballador: ${widget.workerName}',
                    onBack: () => Navigator.of(context).pop(),
                  ),
                  const SizedBox(height: 14),
                  Expanded(
                    child: wide ? _buildWideLayout() : _buildNarrowLayout(),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: _canConfirm ? _confirm : null,
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(58),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                        ),
                      ),
                      child: Text(
                        _submitting ? 'Processant...' : widget.confirmLabel,
                      ),
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
    final double w = MediaQuery.sizeOf(context).width;
    final double pad = w < 800 ? 12.0 : (w < 1100 ? 14.0 : 16.0);
    return Container(
      padding: EdgeInsets.all(pad),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(
            color: Color(0x0F000000),
            blurRadius: 14,
            offset: Offset(0, 5),
          ),
        ],
      ),
      child: child,
    );
  }

  Widget _buildWideLayout() {
    final double gap = MediaQuery.sizeOf(context).width < 800 ? 8 : 14;
    return Row(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        Expanded(flex: 12, child: _buildSummaryPanel()),
        SizedBox(width: gap),
        Expanded(flex: 11, child: _buildPaymentPanel()),
      ],
    );
  }

  Widget _buildNarrowLayout() {
    return Column(
      children: <Widget>[
        Expanded(
          flex: 13,
          child: _buildSummaryPanel(),
        ),
        const SizedBox(height: 10),
        Expanded(
          flex: 11,
          child: _buildPaymentPanel(),
        ),
      ],
    );
  }

  Widget _buildSummaryPanel() {
    return _panelShell(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          const Text(
            'Resum',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900),
          ),
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
                  final bool isBagRow =
                      _bagCount > 0 && i == widget.cartItems.length;
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
                        Text(
                          '${bagTotal.toStringAsFixed(2)}€',
                          style: const TextStyle(fontWeight: FontWeight.w900),
                        ),
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
                      Text(
                        '${item.lineTotal.toStringAsFixed(2)}€',
                        style: const TextStyle(fontWeight: FontWeight.w900),
                      ),
                    ],
                  );
                },
              ),
            ),
          ),
          const SizedBox(height: 12),
          _TwoCol(
            label: 'Base imposable',
            value: (_productsTotal / 1.21).toStringAsFixed(2),
          ),
          _TwoCol(
            label: 'IVA (21%)',
            value: (_productsTotal - (_productsTotal / 1.21)).toStringAsFixed(
              2,
            ),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: <Widget>[
              OutlinedButton.icon(
                onPressed: _bagCount >= widget.bagMaxCount
                    ? null
                    : () => setState(() => _bagCount++),
                icon: const Icon(Icons.shopping_bag_outlined, size: 18),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(140, 46),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                ),
                label: Text(
                  widget.bagUnitPrice > 0
                      ? 'Bossa (+${widget.bagUnitPrice.toStringAsFixed(2)}€)${_bagCount > 0 ? ' ×$_bagCount' : ''}'
                      : 'Bossa${_bagCount > 0 ? ' ×$_bagCount' : ''}',
                ),
              ),
              if (_bagCount > 0)
                OutlinedButton(
                  onPressed: () => setState(() => _bagCount--),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: TpvTheme.danger,
                    minimumSize: const Size(96, 46),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 12,
                    ),
                  ),
                  child: const Text('Treure'),
                ),
              if (widget.showDiscount)
                OutlinedButton(
                  onPressed: () => setState(() => _discount = !_discount),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: _discount
                        ? Colors.white
                        : Colors.green.shade800,
                    side: BorderSide(color: Colors.green.shade800),
                    backgroundColor: _discount
                        ? Colors.green.shade700
                        : Colors.white,
                    minimumSize: const Size(150, 46),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 12,
                    ),
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
              style: const TextStyle(
                fontSize: 34,
                fontWeight: FontWeight.w900,
                color: TpvTheme.primary,
              ),
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
          const Text(
            'Mètode de pagament',
            style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
          ),
          const SizedBox(height: 12),
          Row(
            children: <Widget>[
              Expanded(
                child: _MethodCard(
                  label: '💶 Efectiu',
                  selected: _paymentMethod == 'Efectiu',
                  onTap: () => setState(() => _paymentMethod = 'Efectiu'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _MethodCard(
                  label: '💳 Targeta',
                  selected: _paymentMethod == 'Targeta',
                  onTap: () => setState(() => _paymentMethod = 'Targeta'),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          if (_paymentMethod == 'Efectiu')
            Expanded(child: _buildCashKeypad())
          else
            const Spacer(),
          _buildStatusPanel(),
        ],
      ),
    );
  }

  Widget _buildCashKeypad() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: LayoutBuilder(
        builder: (BuildContext context, BoxConstraints constraints) {
          final double maxHeight = constraints.maxHeight.isFinite
              ? constraints.maxHeight
              : 260;
          final double displayHeight = maxHeight < 250 ? 48 : 56;
          const double buttonRowHeight = 42;
          const double verticalGaps = 20;
          final double availableGridHeight =
              maxHeight - displayHeight - verticalGaps - buttonRowHeight;
          final double gridHeight = availableGridHeight.clamp(132.0, 230.0);
          final double keyWidth = (constraints.maxWidth - 16) / 3;
          final double keyHeight = (gridHeight - 24) / 4;
          final double aspectRatio = keyWidth / keyHeight;

          return Column(
            children: <Widget>[
              Container(
                height: displayHeight,
                padding: const EdgeInsets.symmetric(horizontal: 14),
                decoration: BoxDecoration(
                  color: const Color(0xFFF4F7FF),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE1E6F5)),
                ),
                child: Row(
                  children: <Widget>[
                    const Expanded(
                      child: Text(
                        'Import entregat',
                        style: TextStyle(
                          color: TpvTheme.textSecondary,
                          fontWeight: FontWeight.w800,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    Text(
                      _cashDisplay,
                      style: const TextStyle(
                        color: TpvTheme.textMain,
                        fontWeight: FontWeight.w900,
                        fontSize: 24,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 10),
              SizedBox(
                height: gridHeight,
                child: GridView.count(
                  physics: const NeverScrollableScrollPhysics(),
                  crossAxisCount: 3,
                  crossAxisSpacing: 8,
                  mainAxisSpacing: 8,
                  childAspectRatio: aspectRatio,
                  children: <Widget>[
                    _KeypadButton(label: '7', onTap: () => _appendCashKey('7')),
                    _KeypadButton(label: '8', onTap: () => _appendCashKey('8')),
                    _KeypadButton(label: '9', onTap: () => _appendCashKey('9')),
                    _KeypadButton(label: '4', onTap: () => _appendCashKey('4')),
                    _KeypadButton(label: '5', onTap: () => _appendCashKey('5')),
                    _KeypadButton(label: '6', onTap: () => _appendCashKey('6')),
                    _KeypadButton(label: '1', onTap: () => _appendCashKey('1')),
                    _KeypadButton(label: '2', onTap: () => _appendCashKey('2')),
                    _KeypadButton(label: '3', onTap: () => _appendCashKey('3')),
                    _KeypadButton(label: ',', onTap: () => _appendCashKey(',')),
                    _KeypadButton(label: '0', onTap: () => _appendCashKey('0')),
                    _KeypadButton(
                      icon: Icons.backspace_outlined,
                      onTap: _deleteCashDigit,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 10),
              Row(
                children: <Widget>[
                  Expanded(
                    child: OutlinedButton(
                      onPressed: _clearCash,
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size.fromHeight(42),
                        foregroundColor: TpvTheme.danger,
                      ),
                      child: const Text('Esborrar'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: FilledButton(
                      onPressed: () => _setCashAmount(_finalTotal),
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(42),
                      ),
                      child: Text(_amountLabel(_finalTotal)),
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
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            Text(
              _insufficientCash ? 'Import insuficient' : 'Canvi a retornar',
              style: TextStyle(
                color: accent,
                fontWeight: FontWeight.w800,
                fontSize: 13,
                letterSpacing: 0.2,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              _insufficientCash
                  ? 'Falten ${(_finalTotal - _cashGiven).toStringAsFixed(2).replaceAll('.', ',')}€'
                  : '${_changeToReturn.toStringAsFixed(2).replaceAll('.', ',')}€',
              style: TextStyle(
                color: accent,
                fontWeight: FontWeight.w900,
                fontSize: 27,
                height: 1.1,
              ),
            ),
            if (!_insufficientCash && hasCash) ...<Widget>[
              const SizedBox(height: 4),
              Text(
                'Entregat ${_cashGiven.toStringAsFixed(2).replaceAll('.', ',')}€ · Total ${_finalTotal.toStringAsFixed(2).replaceAll('.', ',')}€',
                style: const TextStyle(
                  color: TpvTheme.textSecondary,
                  fontWeight: FontWeight.w600,
                  fontSize: 12,
                ),
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
        style: const TextStyle(
          color: TpvTheme.textSecondary,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

class _KeypadButton extends StatelessWidget {
  const _KeypadButton({this.label, this.icon, required this.onTap})
    : assert(label != null || icon != null);

  final String? label;
  final IconData? icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(9),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(9),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(9),
            border: Border.all(color: const Color(0xFFE0E3EE)),
            boxShadow: const <BoxShadow>[
              BoxShadow(
                color: Color(0x08000000),
                blurRadius: 8,
                offset: Offset(0, 3),
              ),
            ],
          ),
          alignment: Alignment.center,
          child: icon != null
              ? Icon(icon, color: TpvTheme.textMain, size: 22)
              : Text(
                  label!,
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    color: TpvTheme.textMain,
                  ),
                ),
        ),
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  const _TopBar({
    required this.title,
    required this.subtitle,
    required this.onBack,
  });
  final String title;
  final String subtitle;
  final VoidCallback onBack;
  @override
  Widget build(BuildContext context) {
    final double w = MediaQuery.sizeOf(context).width;
    final double titleSize = w < 800 ? 18.0 : 22.0;
    final double subSize = w < 800 ? 13.0 : 14.0;
    return Row(
      children: <Widget>[
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              FittedBox(
                fit: BoxFit.scaleDown,
                alignment: Alignment.centerLeft,
                child: Text(
                  title,
                  maxLines: 1,
                  style: TextStyle(
                    fontSize: titleSize,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              FittedBox(
                fit: BoxFit.scaleDown,
                alignment: Alignment.centerLeft,
                child: Text(
                  subtitle,
                  maxLines: 2,
                  style: TextStyle(
                    fontSize: subSize,
                    color: TpvTheme.textSecondary,
                  ),
                ),
              ),
            ],
          ),
        ),
        TextButton.icon(
          onPressed: onBack,
          style: TextButton.styleFrom(
            minimumSize: const Size(132, 48),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          ),
          icon: const Icon(Icons.chevron_left_rounded, size: 22),
          label: const Text(
            'Tornar',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          ),
        ),
      ],
    );
  }
}

class _MethodCard extends StatelessWidget {
  const _MethodCard({
    required this.label,
    required this.selected,
    required this.onTap,
  });
  final String label;
  final bool selected;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) {
    final double w = MediaQuery.sizeOf(context).width;
    final double h = w < 800 ? 52.0 : 60.0;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        height: h,
        padding: const EdgeInsets.symmetric(horizontal: 6),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: selected ? TpvTheme.primary : const Color(0xFFE0E3EE),
            width: 2,
          ),
          color: selected ? const Color(0xFFF0F3FF) : Colors.white,
        ),
        child: Center(
          child: FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(
              label,
              textAlign: TextAlign.center,
              maxLines: 2,
            ),
          ),
        ),
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
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: <Widget>[Text(label), Text('$value€')],
    );
  }
}
