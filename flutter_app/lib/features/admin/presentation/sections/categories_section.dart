import 'package:flutter/material.dart';

import '../../../../core/network/api_client.dart';
import '../../../../core/theme/tpv_theme.dart';
import '../../../auth/data/auth_service.dart';
import '../../data/admin_service.dart';
import '../../domain/admin_models.dart';

class CategoriesSection extends StatefulWidget {
  const CategoriesSection({super.key, required this.authService});

  final AuthService authService;

  @override
  State<CategoriesSection> createState() => _CategoriesSectionState();
}

class _CategoriesSectionState extends State<CategoriesSection> {
  late final AdminService _service = AdminService(ApiClient(), widget.authService);

  List<AdminCategory> _categories = <AdminCategory>[];
  bool _loading = true;
  Object? _error;
  bool _busy = false;

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
      final List<AdminCategory> list = await _service.fetchCategories();
      if (!mounted) return;
      setState(() => _categories = list);
    } catch (err) {
      if (!mounted) return;
      setState(() => _error = err);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openEditor({AdminCategory? current}) async {
    final AdminCategory? saved = await showDialog<AdminCategory>(
      context: context,
      builder: (_) => _CategoryEditorDialog(
        service: _service,
        initial: current,
      ),
    );
    if (saved != null) {
      setState(() {
        final int idx = _categories.indexWhere((AdminCategory c) => c.id == saved.id);
        if (idx >= 0) {
          _categories[idx] = saved;
        } else {
          _categories.add(saved);
        }
        _categories.sort((AdminCategory a, AdminCategory b) => a.name.toLowerCase().compareTo(b.name.toLowerCase()));
      });
    }
  }

  Future<void> _confirmDelete(AdminCategory cat) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Eliminar categoria'),
        content: Text('Segur que vols eliminar la categoria "${cat.name}"?'),
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
      await _service.deleteCategory(cat.id);
      if (!mounted) return;
      setState(() => _categories.removeWhere((AdminCategory c) => c.id == cat.id));
      messenger.showSnackBar(SnackBar(content: Text('Categoria "${cat.name}" eliminada')));
    } catch (err) {
      if (!mounted) return;
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
        Row(
          children: <Widget>[
            Expanded(
              child: Text(
                '${_categories.length} categories',
                style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700, fontSize: 14),
              ),
            ),
            FilledButton.icon(
              onPressed: _busy ? null : () => _openEditor(),
              icon: const Icon(Icons.add_rounded, size: 20),
              label: const Text('Nova categoria'),
              style: FilledButton.styleFrom(minimumSize: const Size(180, 46)),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Expanded(child: _buildList()),
      ],
    );
  }

  Widget _buildList() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Icon(Icons.error_outline, size: 48, color: TpvTheme.danger),
            const SizedBox(height: 8),
            Text('$_error', textAlign: TextAlign.center),
            const SizedBox(height: 10),
            OutlinedButton(onPressed: _load, child: const Text('Reintentar')),
          ],
        ),
      );
    }

    if (_categories.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Icon(Icons.category_outlined, size: 56, color: Color(0xFFB0B6C9)),
            const SizedBox(height: 10),
            const Text(
              'Encara no hi ha categories',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: TpvTheme.textMain),
            ),
            const SizedBox(height: 6),
            FilledButton.icon(
              onPressed: () => _openEditor(),
              icon: const Icon(Icons.add_rounded, size: 20),
              label: const Text('Crear la primera'),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFE4E8F4)),
          boxShadow: const <BoxShadow>[
            BoxShadow(color: Color(0x0F000000), blurRadius: 14, offset: Offset(0, 5)),
          ],
        ),
        child: ListView.separated(
          padding: const EdgeInsets.symmetric(vertical: 6),
          itemCount: _categories.length,
          separatorBuilder: (_, _) => const Divider(height: 1, indent: 70, endIndent: 16, color: Color(0xFFEDF0F8)),
          itemBuilder: (BuildContext context, int index) {
            final AdminCategory cat = _categories[index];
            return _CategoryTile(
              category: cat,
              onEdit: _busy ? null : () => _openEditor(current: cat),
              onDelete: _busy ? null : () => _confirmDelete(cat),
            );
          },
        ),
      ),
    );
  }
}

class _CategoryTile extends StatelessWidget {
  const _CategoryTile({
    required this.category,
    required this.onEdit,
    required this.onDelete,
  });

  final AdminCategory category;
  final VoidCallback? onEdit;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    final Color accent = _parseColor(category.color) ?? TpvTheme.primary;
    return InkWell(
      onTap: onEdit,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(
          children: <Widget>[
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: accent.withValues(alpha: 0.14),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: accent.withValues(alpha: 0.35)),
              ),
              alignment: Alignment.center,
              child: Text(
                category.name.isNotEmpty ? category.name.characters.first.toUpperCase() : '?',
                style: TextStyle(fontWeight: FontWeight.w900, color: accent, fontSize: 18),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text(
                    category.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
                  ),
                  Text(
                    category.productsCount == 0
                        ? 'Sense productes'
                        : '${category.productsCount} ${category.productsCount == 1 ? 'producte' : 'productes'}',
                    style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 12),
                  ),
                ],
              ),
            ),
            IconButton(
              onPressed: onEdit,
              icon: const Icon(Icons.edit_outlined, color: TpvTheme.primary),
              tooltip: 'Editar',
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

  static Color? _parseColor(String? raw) {
    if (raw == null || raw.trim().isEmpty) return null;
    String value = raw.trim();
    if (value.startsWith('#')) value = value.substring(1);
    if (value.length == 6) value = 'FF$value';
    if (value.length != 8) return null;
    final int? parsed = int.tryParse(value, radix: 16);
    if (parsed == null) return null;
    return Color(parsed);
  }
}

class _CategoryEditorDialog extends StatefulWidget {
  const _CategoryEditorDialog({required this.service, this.initial});

  final AdminService service;
  final AdminCategory? initial;

  @override
  State<_CategoryEditorDialog> createState() => _CategoryEditorDialogState();
}

class _CategoryEditorDialogState extends State<_CategoryEditorDialog> {
  late final TextEditingController _nameController =
      TextEditingController(text: widget.initial?.name ?? '');
  late final TextEditingController _colorController =
      TextEditingController(text: widget.initial?.color ?? '');
  bool _submitting = false;
  String? _errorText;

  @override
  void dispose() {
    _nameController.dispose();
    _colorController.dispose();
    super.dispose();
  }

  bool get _isEdit => widget.initial != null;

  Future<void> _submit() async {
    final String name = _nameController.text.trim();
    if (name.isEmpty) {
      setState(() => _errorText = 'El nom és obligatori');
      return;
    }
    setState(() {
      _submitting = true;
      _errorText = null;
    });
    try {
      final AdminCategory result;
      if (_isEdit) {
        result = await widget.service.updateCategory(
          id: widget.initial!.id,
          name: name,
          color: _colorController.text.trim().isEmpty ? null : _colorController.text.trim(),
        );
      } else {
        result = await widget.service.createCategory(
          name: name,
          color: _colorController.text.trim().isEmpty ? null : _colorController.text.trim(),
        );
      }
      if (!mounted) return;
      Navigator.of(context).pop(result);
    } catch (err) {
      if (!mounted) return;
      setState(() => _errorText = err.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 460),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(22, 18, 22, 14),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: <Widget>[
              Text(
                _isEdit ? 'Editar categoria' : 'Nova categoria',
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22),
              ),
              const SizedBox(height: 4),
              const Text(
                'Les categories agrupen els productes del catàleg.',
                style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 13),
              ),
              const SizedBox(height: 14),
              TextField(
                controller: _nameController,
                autofocus: true,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(
                  labelText: 'Nom',
                  hintText: 'Ex: Begudes, Hamburgueses...',
                ),
                onSubmitted: (_) => _submit(),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _colorController,
                decoration: const InputDecoration(
                  labelText: 'Color (opcional)',
                  hintText: '#4E73DF',
                  prefixIcon: Icon(Icons.palette_rounded),
                ),
              ),
              if (_errorText != null) ...<Widget>[
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFECEC),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFF3B6B6)),
                  ),
                  child: Row(
                    children: <Widget>[
                      const Icon(Icons.error_outline, color: TpvTheme.danger, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _errorText!,
                          style: const TextStyle(color: TpvTheme.danger, fontWeight: FontWeight.w700, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 18),
              Row(
                children: <Widget>[
                  Expanded(
                    child: OutlinedButton(
                      onPressed: _submitting ? null : () => Navigator.of(context).pop(),
                      child: const Text('Cancel·lar'),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: FilledButton(
                      onPressed: _submitting ? null : _submit,
                      child: Text(_submitting
                          ? 'Guardant...'
                          : _isEdit
                              ? 'Guardar canvis'
                              : 'Crear'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
