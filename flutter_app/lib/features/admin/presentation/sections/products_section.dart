import 'package:cross_file/cross_file.dart';
import 'package:desktop_drop/desktop_drop.dart';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../../core/config/app_config.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/theme/tpv_theme.dart';
import '../../../auth/data/auth_service.dart';
import '../../data/admin_service.dart';
import '../../domain/admin_models.dart';

class ProductsSection extends StatefulWidget {
  const ProductsSection({super.key, required this.authService});

  final AuthService authService;

  @override
  State<ProductsSection> createState() => _ProductsSectionState();
}

class _ProductsSectionState extends State<ProductsSection> {
  late final AdminService _service = AdminService(ApiClient(), widget.authService);

  List<AdminProduct> _products = <AdminProduct>[];
  List<AdminCategory> _categories = <AdminCategory>[];
  bool _loading = true;
  Object? _error;
  bool _busy = false;

  int? _filterCategoryId;
  String _search = '';

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
      final List<AdminProduct> products = await _service.fetchProducts();
      final List<AdminCategory> categories = await _service.fetchCategories();
      if (!mounted) return;
      setState(() {
        _products = products;
        _categories = categories;
      });
    } catch (err) {
      if (!mounted) return;
      setState(() => _error = err);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openEditor({AdminProduct? current}) async {
    if (_categories.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Primer crea com a mínim una categoria')),
      );
      return;
    }
    final AdminProduct? saved = await showDialog<AdminProduct>(
      context: context,
      builder: (_) => _ProductEditorDialog(
        service: _service,
        categories: _categories,
        initial: current,
      ),
    );
    if (saved != null) {
      setState(() {
        final int idx = _products.indexWhere((AdminProduct p) => p.id == saved.id);
        if (idx >= 0) {
          _products[idx] = saved;
        } else {
          _products.add(saved);
        }
        _products.sort((AdminProduct a, AdminProduct b) =>
            a.name.toLowerCase().compareTo(b.name.toLowerCase()));
      });
    }
  }

  Future<void> _confirmDelete(AdminProduct p) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Eliminar producte'),
        content: Text('Segur que vols eliminar "${p.name}"?'),
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
      await _service.deleteProduct(p.id);
      if (!mounted) return;
      setState(() => _products.removeWhere((AdminProduct x) => x.id == p.id));
      messenger.showSnackBar(SnackBar(content: Text('Producte "${p.name}" eliminat')));
    } catch (err) {
      if (!mounted) return;
      messenger.showSnackBar(SnackBar(content: Text('$err')));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _toggleActive(AdminProduct p) async {
    setState(() => _busy = true);
    try {
      final AdminProduct updated = await _service.saveProduct(
        id: p.id,
        name: p.name,
        price: p.price,
        stock: p.stock,
        categoryId: p.categoryId ?? 0,
        isGlutenFree: p.isGlutenFree,
        active: !p.active,
        description: p.description,
        imagePath: p.imagePath,
      );
      if (!mounted) return;
      setState(() {
        final int idx = _products.indexWhere((AdminProduct x) => x.id == updated.id);
        if (idx >= 0) _products[idx] = updated;
      });
    } catch (err) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$err')));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  List<AdminProduct> get _filtered {
    Iterable<AdminProduct> list = _products;
    if (_filterCategoryId != null) {
      list = list.where((AdminProduct p) => p.categoryId == _filterCategoryId);
    }
    if (_search.trim().isNotEmpty) {
      final String q = _search.trim().toLowerCase();
      list = list.where((AdminProduct p) => p.name.toLowerCase().contains(q));
    }
    return list.toList();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        _buildToolbar(),
        const SizedBox(height: 12),
        Expanded(child: _buildGrid()),
      ],
    );
  }

  Widget _buildToolbar() {
    return LayoutBuilder(builder: (BuildContext _, BoxConstraints c) {
      final bool wide = c.maxWidth >= 760;
      final Widget search = SizedBox(
        height: 46,
        width: wide ? 280 : double.infinity,
        child: TextField(
          onChanged: (String v) => setState(() => _search = v),
          decoration: InputDecoration(
            hintText: 'Buscar producte...',
            prefixIcon: const Icon(Icons.search_rounded),
            isDense: true,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFFE4E8F4)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Color(0xFFE4E8F4)),
            ),
          ),
        ),
      );
      final Widget add = FilledButton.icon(
        onPressed: _busy ? null : () => _openEditor(),
        icon: const Icon(Icons.add_rounded, size: 20),
        label: const Text('Nou producte'),
        style: FilledButton.styleFrom(minimumSize: const Size(180, 46)),
      );
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: <Widget>[
          Row(
            children: <Widget>[
              Expanded(
                child: Text(
                  '${_filtered.length} de ${_products.length} productes',
                  style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700, fontSize: 14),
                ),
              ),
              if (wide) ...<Widget>[search, const SizedBox(width: 10), add],
            ],
          ),
          if (!wide) ...<Widget>[
            const SizedBox(height: 10),
            search,
            const SizedBox(height: 10),
            add,
          ],
          const SizedBox(height: 10),
          _buildCategoryChips(),
        ],
      );
    });
  }

  Widget _buildCategoryChips() {
    return SizedBox(
      height: 40,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _categories.length + 1,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (BuildContext _, int i) {
          if (i == 0) {
            final bool selected = _filterCategoryId == null;
            return _FilterChip(
              label: 'Totes',
              color: TpvTheme.primary,
              selected: selected,
              onTap: () => setState(() => _filterCategoryId = null),
            );
          }
          final AdminCategory c = _categories[i - 1];
          final bool selected = _filterCategoryId == c.id;
          return _FilterChip(
            label: c.name,
            color: _parseColor(c.color) ?? TpvTheme.primary,
            selected: selected,
            onTap: () => setState(() => _filterCategoryId = c.id),
          );
        },
      ),
    );
  }

  Widget _buildGrid() {
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
    final List<AdminProduct> list = _filtered;
    if (list.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Icon(Icons.inventory_2_outlined, size: 56, color: Color(0xFFB0B6C9)),
            const SizedBox(height: 10),
            const Text(
              'Sense productes que coincideixin',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: TpvTheme.textMain),
            ),
            const SizedBox(height: 6),
            FilledButton.icon(
              onPressed: () => _openEditor(),
              icon: const Icon(Icons.add_rounded, size: 20),
              label: const Text('Nou producte'),
            ),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: LayoutBuilder(builder: (BuildContext _, BoxConstraints c) {
        final int cols = c.maxWidth >= 1400
            ? 4
            : c.maxWidth >= 1000
                ? 3
                : c.maxWidth >= 640
                    ? 2
                    : 1;
        return GridView.builder(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(vertical: 4),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: cols,
            mainAxisExtent: 168,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
          ),
          itemCount: list.length,
          itemBuilder: (BuildContext _, int i) {
            final AdminProduct p = list[i];
            return _ProductCard(
              product: p,
              onTap: _busy ? null : () => _openEditor(current: p),
              onDelete: _busy ? null : () => _confirmDelete(p),
              onToggle: _busy ? null : () => _toggleActive(p),
            );
          },
        );
      }),
    );
  }
}

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.color,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final Color color;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(24),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? color : Colors.white,
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: selected ? color : const Color(0xFFE4E8F4)),
        ),
        alignment: Alignment.center,
        child: Text(
          label,
          style: TextStyle(
            color: selected ? Colors.white : TpvTheme.textMain,
            fontWeight: FontWeight.w800,
            fontSize: 13,
          ),
        ),
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  const _ProductCard({
    required this.product,
    required this.onTap,
    required this.onDelete,
    required this.onToggle,
  });

  final AdminProduct product;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  final VoidCallback? onToggle;

  @override
  Widget build(BuildContext context) {
    final Color accent = _parseColor(product.categoryColor) ?? TpvTheme.primary;
    return Material(
      color: Colors.white,
      elevation: 0,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
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
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: accent.withValues(alpha: 0.14),
                      borderRadius: BorderRadius.circular(999),
                      border: Border.all(color: accent.withValues(alpha: 0.35)),
                    ),
                    child: Text(
                      product.categoryName ?? 'Sense categoria',
                      style: TextStyle(color: accent, fontWeight: FontWeight.w800, fontSize: 11),
                    ),
                  ),
                  const Spacer(),
                  if (!product.active)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFECEC),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: const Text(
                        'Inactiu',
                        style: TextStyle(color: TpvTheme.danger, fontWeight: FontWeight.w900, fontSize: 10),
                      ),
                    ),
                  if (product.isGlutenFree)
                    Padding(
                      padding: const EdgeInsets.only(left: 6),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE8F7EE),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: const Text(
                          'Sense gluten',
                          style: TextStyle(color: Color(0xFF1C8B43), fontWeight: FontWeight.w900, fontSize: 10),
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                product.name,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: TpvTheme.textMain),
              ),
              const Spacer(),
              Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: <Widget>[
                  Text(
                    '${product.price.toStringAsFixed(2)}€',
                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22, color: TpvTheme.primary),
                  ),
                  const SizedBox(width: 8),
                  Padding(
                    padding: const EdgeInsets.only(bottom: 4),
                    child: Text(
                      'Stock: ${product.stock}',
                      style: const TextStyle(
                        color: TpvTheme.textSecondary,
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  const Spacer(),
                  IconButton(
                    onPressed: onToggle,
                    icon: Icon(
                      product.active ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                      color: product.active ? const Color(0xFF1C8B43) : TpvTheme.textSecondary,
                    ),
                    iconSize: 20,
                    tooltip: product.active ? 'Desactivar' : 'Activar',
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                  ),
                  IconButton(
                    onPressed: onDelete,
                    icon: const Icon(Icons.delete_outline, color: TpvTheme.danger),
                    iconSize: 20,
                    tooltip: 'Eliminar',
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
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

class _ProductEditorDialog extends StatefulWidget {
  const _ProductEditorDialog({
    required this.service,
    required this.categories,
    this.initial,
  });

  final AdminService service;
  final List<AdminCategory> categories;
  final AdminProduct? initial;

  @override
  State<_ProductEditorDialog> createState() => _ProductEditorDialogState();
}

class _ProductEditorDialogState extends State<_ProductEditorDialog> {
  late final TextEditingController _nameController =
      TextEditingController(text: widget.initial?.name ?? '');
  late final TextEditingController _priceController =
      TextEditingController(text: widget.initial?.price.toStringAsFixed(2) ?? '');
  late final TextEditingController _stockController =
      TextEditingController(text: widget.initial?.stock.toString() ?? '0');
  late final TextEditingController _descriptionController =
      TextEditingController(text: widget.initial?.description ?? '');

  late int? _categoryId = widget.initial?.categoryId ?? widget.categories.first.id;
  late bool _isGlutenFree = widget.initial?.isGlutenFree ?? false;
  late bool _active = widget.initial?.active ?? true;
  late String? _imagePath = widget.initial?.imagePath;
  bool _submitting = false;
  bool _uploading = false;
  String? _errorText;

  @override
  void dispose() {
    _nameController.dispose();
    _priceController.dispose();
    _stockController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  bool get _isEdit => widget.initial != null;

  Future<void> _pickImageFile() async {
    try {
      final FilePickerResult? result = await FilePicker.pickFiles(
        type: FileType.image,
        withData: true,
      );
      if (result == null || result.files.isEmpty) return;
      final PlatformFile file = result.files.first;
      if (file.bytes == null) {
        setState(() => _errorText = 'No s\'ha pogut llegir l\'arxiu');
        return;
      }
      await _uploadImage(bytes: file.bytes!, filename: file.name);
    } catch (err) {
      if (!mounted) return;
      setState(() => _errorText = err.toString().replaceAll('Exception: ', ''));
    }
  }

  Future<void> _handleDroppedFile(XFile file) async {
    try {
      final List<int> bytes = await file.readAsBytes();
      await _uploadImage(bytes: bytes, filename: file.name);
    } catch (err) {
      if (!mounted) return;
      setState(() => _errorText = err.toString().replaceAll('Exception: ', ''));
    }
  }

  Future<void> _uploadImage({required List<int> bytes, required String filename}) async {
    if (!_isImageFilename(filename)) {
      setState(() => _errorText = 'Només s\'admeten imatges (JPG, PNG, WEBP, GIF)');
      return;
    }
    setState(() {
      _uploading = true;
      _errorText = null;
    });
    try {
      final String path = await widget.service.uploadProductImage(
        bytes: bytes,
        filename: filename,
      );
      if (!mounted) return;
      setState(() => _imagePath = path);
    } catch (err) {
      if (!mounted) return;
      setState(() => _errorText = err.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  bool _isImageFilename(String name) {
    final String lower = name.toLowerCase();
    return lower.endsWith('.jpg') ||
        lower.endsWith('.jpeg') ||
        lower.endsWith('.png') ||
        lower.endsWith('.webp') ||
        lower.endsWith('.gif');
  }

  Future<void> _submit() async {
    final String name = _nameController.text.trim();
    final double? price = double.tryParse(_priceController.text.replaceAll(',', '.').trim());
    final int stock = int.tryParse(_stockController.text.trim()) ?? 0;
    if (name.isEmpty) {
      setState(() => _errorText = 'El nom és obligatori');
      return;
    }
    if (price == null || price < 0) {
      setState(() => _errorText = 'Preu invàlid');
      return;
    }
    if (_categoryId == null) {
      setState(() => _errorText = 'Selecciona una categoria');
      return;
    }
    setState(() {
      _submitting = true;
      _errorText = null;
    });
    try {
      final AdminProduct saved = await widget.service.saveProduct(
        id: widget.initial?.id,
        name: name,
        price: price,
        stock: stock,
        categoryId: _categoryId!,
        isGlutenFree: _isGlutenFree,
        active: _active,
        description: _descriptionController.text.trim().isEmpty
            ? null
            : _descriptionController.text.trim(),
        imagePath: (_imagePath ?? '').trim().isEmpty ? null : _imagePath!.trim(),
      );
      if (!mounted) return;
      Navigator.of(context).pop(saved);
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
        constraints: const BoxConstraints(maxWidth: 560),
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(22, 18, 22, 14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: <Widget>[
              Text(
                _isEdit ? 'Editar producte' : 'Nou producte',
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22),
              ),
              const SizedBox(height: 14),
              TextField(
                controller: _nameController,
                autofocus: true,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(labelText: 'Nom'),
              ),
              const SizedBox(height: 12),
              Row(
                children: <Widget>[
                  Expanded(
                    child: TextField(
                      controller: _priceController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      inputFormatters: <TextInputFormatter>[
                        FilteringTextInputFormatter.allow(RegExp(r'[0-9.,]')),
                      ],
                      decoration: const InputDecoration(labelText: 'Preu (€)'),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextField(
                      controller: _stockController,
                      keyboardType: TextInputType.number,
                      inputFormatters: <TextInputFormatter>[
                        FilteringTextInputFormatter.digitsOnly,
                      ],
                      decoration: const InputDecoration(labelText: 'Estoc'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<int>(
                initialValue: _categoryId,
                decoration: const InputDecoration(labelText: 'Categoria'),
                items: widget.categories
                    .map((AdminCategory c) => DropdownMenuItem<int>(
                          value: c.id,
                          child: Text(c.name),
                        ))
                    .toList(),
                onChanged: (int? v) => setState(() => _categoryId = v),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _descriptionController,
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Descripció (opcional)',
                ),
              ),
              const SizedBox(height: 14),
              _ImageDropzone(
                path: _imagePath,
                uploading: _uploading,
                onPick: _pickImageFile,
                onDropped: _handleDroppedFile,
                onClear: () => setState(() => _imagePath = null),
              ),
              const SizedBox(height: 8),
              SwitchListTile.adaptive(
                contentPadding: EdgeInsets.zero,
                value: _isGlutenFree,
                onChanged: (bool v) => setState(() => _isGlutenFree = v),
                title: const Text('Sense gluten', style: TextStyle(fontWeight: FontWeight.w700)),
              ),
              SwitchListTile.adaptive(
                contentPadding: EdgeInsets.zero,
                value: _active,
                onChanged: (bool v) => setState(() => _active = v),
                title: const Text('Visible al catàleg', style: TextStyle(fontWeight: FontWeight.w700)),
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

Color? _parseColor(String? raw) {
  if (raw == null || raw.trim().isEmpty) return null;
  String value = raw.trim();
  if (value.startsWith('#')) value = value.substring(1);
  if (value.length == 6) value = 'FF$value';
  if (value.length != 8) return null;
  final int? parsed = int.tryParse(value, radix: 16);
  if (parsed == null) return null;
  return Color(parsed);
}

class _ImageDropzone extends StatefulWidget {
  const _ImageDropzone({
    required this.path,
    required this.uploading,
    required this.onPick,
    required this.onDropped,
    required this.onClear,
  });

  final String? path;
  final bool uploading;
  final VoidCallback onPick;
  final Future<void> Function(XFile file) onDropped;
  final VoidCallback onClear;

  @override
  State<_ImageDropzone> createState() => _ImageDropzoneState();
}

class _ImageDropzoneState extends State<_ImageDropzone> {
  bool _dragging = false;

  @override
  Widget build(BuildContext context) {
    final bool hasImage = widget.path != null && widget.path!.trim().isNotEmpty;

    return DropTarget(
      onDragEntered: (_) => setState(() => _dragging = true),
      onDragExited: (_) => setState(() => _dragging = false),
      onDragDone: (DropDoneDetails details) async {
        setState(() => _dragging = false);
        if (details.files.isEmpty) return;
        await widget.onDropped(details.files.first);
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        height: hasImage ? 200 : 160,
        decoration: BoxDecoration(
          color: _dragging ? TpvTheme.primary.withValues(alpha: 0.08) : const Color(0xFFF6F8FD),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: _dragging
                ? TpvTheme.primary
                : hasImage
                    ? const Color(0xFFCBD2E4)
                    : const Color(0xFFD7DCE8),
            width: _dragging ? 2 : 1.2,
            style: hasImage ? BorderStyle.solid : BorderStyle.solid,
          ),
        ),
        child: hasImage ? _buildPreview() : _buildEmpty(),
      ),
    );
  }

  Widget _buildPreview() {
    final String url = AppConfig.resolveAsset(widget.path!);
    return ClipRRect(
      borderRadius: BorderRadius.circular(15),
      child: Stack(
        fit: StackFit.expand,
        children: <Widget>[
          Image.network(
            url,
            fit: BoxFit.cover,
            errorBuilder: (_, _, _) => Container(
              color: const Color(0xFFF0F2F8),
              alignment: Alignment.center,
              child: const Column(
                mainAxisSize: MainAxisSize.min,
                children: <Widget>[
                  Icon(Icons.broken_image_outlined, size: 36, color: Color(0xFFB0B6C9)),
                  SizedBox(height: 4),
                  Text(
                    'No s\'ha pogut carregar la imatge',
                    style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
            loadingBuilder: (BuildContext _, Widget child, ImageChunkEvent? progress) {
              if (progress == null) return child;
              return Container(
                color: const Color(0xFFF0F2F8),
                alignment: Alignment.center,
                child: const CircularProgressIndicator(strokeWidth: 2),
              );
            },
          ),
          if (widget.uploading)
            Container(
              color: Colors.black.withValues(alpha: 0.35),
              alignment: Alignment.center,
              child: const CircularProgressIndicator(color: Colors.white),
            ),
          Positioned(
            top: 8,
            right: 8,
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: <Widget>[
                _iconAction(
                  icon: Icons.swap_horiz_rounded,
                  tooltip: 'Reemplaçar',
                  onTap: widget.uploading ? null : widget.onPick,
                ),
                const SizedBox(width: 6),
                _iconAction(
                  icon: Icons.close_rounded,
                  tooltip: 'Treure imatge',
                  color: TpvTheme.danger,
                  onTap: widget.uploading ? null : widget.onClear,
                ),
              ],
            ),
          ),
          Positioned(
            left: 10,
            bottom: 10,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: Colors.black.withValues(alpha: 0.5),
                borderRadius: BorderRadius.circular(999),
              ),
              child: Text(
                widget.path!.split('/').last,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 11),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _iconAction({
    required IconData icon,
    required String tooltip,
    required VoidCallback? onTap,
    Color? color,
  }) {
    return Material(
      color: Colors.white.withValues(alpha: 0.92),
      borderRadius: BorderRadius.circular(999),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(999),
        child: Padding(
          padding: const EdgeInsets.all(6),
          child: Tooltip(
            message: tooltip,
            child: Icon(icon, size: 18, color: color ?? TpvTheme.textMain),
          ),
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return InkWell(
      onTap: widget.uploading ? null : widget.onPick,
      borderRadius: BorderRadius.circular(15),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            if (widget.uploading)
              const SizedBox(
                width: 32,
                height: 32,
                child: CircularProgressIndicator(strokeWidth: 2.4),
              )
            else
              Icon(
                _dragging ? Icons.file_download_outlined : Icons.cloud_upload_outlined,
                size: 34,
                color: _dragging ? TpvTheme.primary : const Color(0xFF8C93A8),
              ),
            const SizedBox(height: 8),
            Text(
              widget.uploading
                  ? 'Pujant imatge...'
                  : _dragging
                      ? 'Deixa anar per pujar'
                      : 'Arrossega una imatge o fes clic per triar-la',
              style: TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 14,
                color: _dragging ? TpvTheme.primary : TpvTheme.textMain,
              ),
            ),
            const SizedBox(height: 4),
            const Text(
              'JPG · PNG · WEBP · GIF (màx. 4 MB)',
              style: TextStyle(
                color: TpvTheme.textSecondary,
                fontWeight: FontWeight.w600,
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
