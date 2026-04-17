import 'package:flutter/material.dart';

import '../../../core/theme/tpv_theme.dart';
import '../../auth/data/auth_service.dart';
import 'sections/caixa_section.dart';
import 'sections/categories_section.dart';
import 'sections/dashboard_section.dart';
import 'sections/history_section.dart';
import 'sections/products_section.dart';
import 'sections/workers_section.dart';

enum AdminSection { resum, caixa, categories, productes, treballadors, historial }

class AdminPage extends StatefulWidget {
  const AdminPage({
    super.key,
    required this.authService,
    required this.adminName,
  });

  final AuthService authService;
  final String adminName;

  @override
  State<AdminPage> createState() => _AdminPageState();
}

class _AdminPageState extends State<AdminPage> {
  AdminSection _current = AdminSection.resum;

  @override
  Widget build(BuildContext context) {
    final double width = MediaQuery.of(context).size.width;
    final bool compact = width < 900;

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
                if (!compact) ...<Widget>[
                  _AdminSidebar(
                    current: _current,
                    adminName: widget.adminName,
                    onSelect: (AdminSection s) => setState(() => _current = s),
                    onBackToTpv: () => Navigator.of(context).pop(),
                  ),
                  const SizedBox(width: 12),
                ],
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.86),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: const Color(0xFFE4E8F4)),
                      boxShadow: const <BoxShadow>[
                        BoxShadow(color: Color(0x10000000), blurRadius: 20, offset: Offset(0, 8)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: <Widget>[
                        _AdminHeader(
                          section: _current,
                          adminName: widget.adminName,
                          compact: compact,
                          onOpenMenu: compact
                              ? () => _openCompactMenu(context)
                              : null,
                          onBackToTpv: () => Navigator.of(context).pop(),
                        ),
                        const SizedBox(height: 14),
                        Expanded(child: _buildBody()),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildBody() {
    switch (_current) {
      case AdminSection.resum:
        return DashboardSection(authService: widget.authService);
      case AdminSection.caixa:
        return CaixaSection(authService: widget.authService);
      case AdminSection.categories:
        return CategoriesSection(authService: widget.authService);
      case AdminSection.productes:
        return ProductsSection(authService: widget.authService);
      case AdminSection.treballadors:
        return WorkersSection(authService: widget.authService);
      case AdminSection.historial:
        return HistorySection(authService: widget.authService);
    }
  }

  Future<void> _openCompactMenu(BuildContext context) async {
    final AdminSection? picked = await showModalBottomSheet<AdminSection>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: AdminSection.values
                  .map((AdminSection s) => ListTile(
                        leading: Icon(_iconFor(s), color: TpvTheme.primary),
                        title: Text(_labelFor(s), style: const TextStyle(fontWeight: FontWeight.w700)),
                        trailing: _current == s ? const Icon(Icons.check, color: TpvTheme.primary) : null,
                        onTap: () => Navigator.of(context).pop(s),
                      ))
                  .toList(),
            ),
          ),
        );
      },
    );
    if (picked != null) setState(() => _current = picked);
  }
}

String _labelFor(AdminSection s) {
  switch (s) {
    case AdminSection.resum:
      return 'Resum';
    case AdminSection.caixa:
      return 'Tancament de Caixa';
    case AdminSection.categories:
      return 'Categories';
    case AdminSection.productes:
      return 'Productes';
    case AdminSection.treballadors:
      return 'Treballadors';
    case AdminSection.historial:
      return 'Historial de Vendes';
  }
}

IconData _iconFor(AdminSection s) {
  switch (s) {
    case AdminSection.resum:
      return Icons.dashboard_rounded;
    case AdminSection.caixa:
      return Icons.account_balance_wallet_rounded;
    case AdminSection.categories:
      return Icons.category_rounded;
    case AdminSection.productes:
      return Icons.fastfood_rounded;
    case AdminSection.treballadors:
      return Icons.people_alt_rounded;
    case AdminSection.historial:
      return Icons.receipt_long_rounded;
  }
}

class _AdminSidebar extends StatelessWidget {
  const _AdminSidebar({
    required this.current,
    required this.adminName,
    required this.onSelect,
    required this.onBackToTpv,
  });

  final AdminSection current;
  final String adminName;
  final ValueChanged<AdminSection> onSelect;
  final VoidCallback onBackToTpv;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 260,
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 16),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.86),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFE4E8F4)),
        boxShadow: const <BoxShadow>[
          BoxShadow(color: Color(0x12000000), blurRadius: 16, offset: Offset(0, 6)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: <Widget>[
          Row(
            children: <Widget>[
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: <Color>[Color(0xFF5D7FE7), TpvTheme.primary],
                  ),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.shield_moon_rounded, color: Colors.white),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: <Widget>[
                    const Text('Admin', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
                    Text(
                      adminName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 12),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Expanded(
            child: ListView(
              children: <Widget>[
                _GroupLabel(label: 'Panell'),
                _SidebarItem(
                  icon: _iconFor(AdminSection.resum),
                  label: _labelFor(AdminSection.resum),
                  active: current == AdminSection.resum,
                  onTap: () => onSelect(AdminSection.resum),
                ),
                const SizedBox(height: 4),
                _GroupLabel(label: 'Catàleg'),
                _SidebarItem(
                  icon: _iconFor(AdminSection.categories),
                  label: _labelFor(AdminSection.categories),
                  active: current == AdminSection.categories,
                  onTap: () => onSelect(AdminSection.categories),
                ),
                _SidebarItem(
                  icon: _iconFor(AdminSection.productes),
                  label: _labelFor(AdminSection.productes),
                  active: current == AdminSection.productes,
                  onTap: () => onSelect(AdminSection.productes),
                ),
                const SizedBox(height: 4),
                _GroupLabel(label: 'Equip'),
                _SidebarItem(
                  icon: _iconFor(AdminSection.treballadors),
                  label: _labelFor(AdminSection.treballadors),
                  active: current == AdminSection.treballadors,
                  onTap: () => onSelect(AdminSection.treballadors),
                ),
                const SizedBox(height: 4),
                _GroupLabel(label: 'Caixa i Vendes'),
                _SidebarItem(
                  icon: _iconFor(AdminSection.caixa),
                  label: _labelFor(AdminSection.caixa),
                  active: current == AdminSection.caixa,
                  onTap: () => onSelect(AdminSection.caixa),
                ),
                _SidebarItem(
                  icon: _iconFor(AdminSection.historial),
                  label: _labelFor(AdminSection.historial),
                  active: current == AdminSection.historial,
                  onTap: () => onSelect(AdminSection.historial),
                ),
              ],
            ),
          ),
          const Divider(height: 22),
          OutlinedButton.icon(
            onPressed: onBackToTpv,
            icon: const Icon(Icons.chevron_left_rounded, size: 20),
            label: const Text('Tornar al TPV'),
            style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(48)),
          ),
        ],
      ),
    );
  }
}

class _GroupLabel extends StatelessWidget {
  const _GroupLabel({required this.label});
  final String label;
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(10, 10, 10, 6),
      child: Text(
        label.toUpperCase(),
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w800,
          letterSpacing: 1.2,
          color: Color(0xFF9BA2B8),
        ),
      ),
    );
  }
}

class _SidebarItem extends StatelessWidget {
  const _SidebarItem({
    required this.icon,
    required this.label,
    required this.active,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            gradient: active
                ? const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: <Color>[Color(0xFF5D7FE7), TpvTheme.primary],
                  )
                : null,
            color: active ? null : Colors.transparent,
            boxShadow: active
                ? const <BoxShadow>[
                    BoxShadow(color: Color(0x334E73DF), blurRadius: 12, offset: Offset(0, 5)),
                  ]
                : null,
          ),
          child: Row(
            children: <Widget>[
              Icon(icon, size: 20, color: active ? Colors.white : TpvTheme.textSecondary),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 15,
                    color: active ? Colors.white : TpvTheme.textMain,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _AdminHeader extends StatelessWidget {
  const _AdminHeader({
    required this.section,
    required this.adminName,
    required this.compact,
    required this.onBackToTpv,
    this.onOpenMenu,
  });

  final AdminSection section;
  final String adminName;
  final bool compact;
  final VoidCallback onBackToTpv;
  final VoidCallback? onOpenMenu;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: <Widget>[
        if (compact) ...<Widget>[
          IconButton(
            onPressed: onOpenMenu,
            icon: const Icon(Icons.menu_rounded),
          ),
          const SizedBox(width: 4),
        ],
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Text(
                _labelFor(section),
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 28),
              ),
              Text(
                'Admin · $adminName',
                style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 13),
              ),
            ],
          ),
        ),
        if (compact)
          TextButton.icon(
            onPressed: onBackToTpv,
            icon: const Icon(Icons.chevron_left_rounded),
            label: const Text('TPV'),
          ),
      ],
    );
  }
}

