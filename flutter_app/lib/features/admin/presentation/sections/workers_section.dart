import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../../core/network/api_client.dart';
import '../../../../core/theme/tpv_theme.dart';
import '../../../auth/data/auth_service.dart';
import '../../data/admin_service.dart';
import '../../domain/admin_models.dart';

class WorkersSection extends StatefulWidget {
  const WorkersSection({super.key, required this.authService});

  final AuthService authService;

  @override
  State<WorkersSection> createState() => _WorkersSectionState();
}

class _WorkersSectionState extends State<WorkersSection> {
  late final AdminService _service = AdminService(ApiClient(), widget.authService);

  List<AdminWorker> _workers = <AdminWorker>[];
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
      final List<AdminWorker> list = await _service.fetchWorkers();
      if (!mounted) return;
      setState(() => _workers = list);
    } catch (err) {
      if (!mounted) return;
      setState(() => _error = err);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openEditor({AdminWorker? current}) async {
    final AdminWorker? saved = await showDialog<AdminWorker>(
      context: context,
      builder: (_) => _WorkerEditorDialog(
        service: _service,
        initial: current,
      ),
    );
    if (saved != null) {
      setState(() {
        final int idx = _workers.indexWhere((AdminWorker w) => w.id == saved.id);
        if (idx >= 0) {
          _workers[idx] = saved;
        } else {
          _workers.add(saved);
        }
        _workers.sort((AdminWorker a, AdminWorker b) =>
            a.name.toLowerCase().compareTo(b.name.toLowerCase()));
      });
    }
  }

  Future<void> _confirmDelete(AdminWorker w) async {
    final bool? confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Eliminar treballador'),
        content: Text('Segur que vols eliminar "${w.name}"?'),
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
      await _service.deleteWorker(w.id);
      if (!mounted) return;
      setState(() => _workers.removeWhere((AdminWorker x) => x.id == w.id));
      messenger.showSnackBar(SnackBar(content: Text('Treballador "${w.name}" eliminat')));
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
                '${_workers.length} treballadors',
                style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w700, fontSize: 14),
              ),
            ),
            FilledButton.icon(
              onPressed: _busy ? null : () => _openEditor(),
              icon: const Icon(Icons.person_add_alt_1_rounded, size: 20),
              label: const Text('Nou treballador'),
              style: FilledButton.styleFrom(minimumSize: const Size(200, 46)),
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
    if (_workers.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const Icon(Icons.badge_outlined, size: 56, color: Color(0xFFB0B6C9)),
            const SizedBox(height: 10),
            const Text(
              'Sense treballadors',
              style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: TpvTheme.textMain),
            ),
            const SizedBox(height: 6),
            FilledButton.icon(
              onPressed: () => _openEditor(),
              icon: const Icon(Icons.person_add_alt_1_rounded, size: 20),
              label: const Text('Crear el primer'),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: LayoutBuilder(builder: (BuildContext _, BoxConstraints c) {
        final int cols = c.maxWidth >= 1200 ? 3 : c.maxWidth >= 720 ? 2 : 1;
        return GridView.builder(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(vertical: 4),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: cols,
            mainAxisExtent: 136,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
          ),
          itemCount: _workers.length,
          itemBuilder: (BuildContext _, int i) {
            final AdminWorker w = _workers[i];
            return _WorkerCard(
              worker: w,
              onEdit: _busy ? null : () => _openEditor(current: w),
              onDelete: _busy ? null : () => _confirmDelete(w),
            );
          },
        );
      }),
    );
  }
}

class _WorkerCard extends StatelessWidget {
  const _WorkerCard({
    required this.worker,
    required this.onEdit,
    required this.onDelete,
  });

  final AdminWorker worker;
  final VoidCallback? onEdit;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    final Color accent = worker.hasPin ? const Color(0xFFF59E0B) : TpvTheme.primary;
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onEdit,
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
          child: Row(
            children: <Widget>[
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  color: accent.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: accent.withValues(alpha: 0.35)),
                ),
                alignment: Alignment.center,
                child: Text(
                  worker.name.isNotEmpty ? worker.name.characters.first.toUpperCase() : '?',
                  style: TextStyle(fontWeight: FontWeight.w900, color: accent, fontSize: 22),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: <Widget>[
                    Text(
                      worker.name,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      worker.ordersCount == 0
                          ? 'Cap comanda registrada'
                          : '${worker.ordersCount} ${worker.ordersCount == 1 ? 'comanda' : 'comandes'}',
                      style: const TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 12),
                    ),
                    const SizedBox(height: 6),
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: <Widget>[
                        if (worker.hasPin)
                          _badge('Admin · PIN', accent)
                        else
                          _badge('Treballador', TpvTheme.primary),
                        if (!worker.active) _badge('Inactiu', TpvTheme.danger),
                      ],
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: onDelete,
                icon: const Icon(Icons.delete_outline, color: TpvTheme.danger),
                tooltip: 'Eliminar',
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _badge(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        text,
        style: TextStyle(color: color, fontWeight: FontWeight.w900, fontSize: 10),
      ),
    );
  }
}

class _WorkerEditorDialog extends StatefulWidget {
  const _WorkerEditorDialog({required this.service, this.initial});

  final AdminService service;
  final AdminWorker? initial;

  @override
  State<_WorkerEditorDialog> createState() => _WorkerEditorDialogState();
}

class _WorkerEditorDialogState extends State<_WorkerEditorDialog> {
  late final TextEditingController _nameController =
      TextEditingController(text: widget.initial?.name ?? '');
  late final TextEditingController _pinController =
      TextEditingController(text: widget.initial?.pin ?? '');
  late bool _active = widget.initial?.active ?? true;
  bool _submitting = false;
  String? _errorText;

  @override
  void dispose() {
    _nameController.dispose();
    _pinController.dispose();
    super.dispose();
  }

  bool get _isEdit => widget.initial != null;

  Future<void> _submit() async {
    final String name = _nameController.text.trim();
    final String pin = _pinController.text.trim();
    if (name.isEmpty) {
      setState(() => _errorText = 'El nom és obligatori');
      return;
    }
    if (pin.isNotEmpty && pin.length != 4) {
      setState(() => _errorText = 'El PIN ha de tenir 4 dígits');
      return;
    }
    setState(() {
      _submitting = true;
      _errorText = null;
    });
    try {
      final AdminWorker saved = await widget.service.saveWorker(
        id: widget.initial?.id,
        name: name,
        pin: pin.isEmpty ? null : pin,
        active: _active,
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
        constraints: const BoxConstraints(maxWidth: 460),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(22, 18, 22, 14),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: <Widget>[
              Text(
                _isEdit ? 'Editar treballador' : 'Nou treballador',
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 22),
              ),
              const SizedBox(height: 4),
              const Text(
                'Els treballadors amb PIN tenen accés al panell d\'administració.',
                style: TextStyle(color: TpvTheme.textSecondary, fontWeight: FontWeight.w600, fontSize: 13),
              ),
              const SizedBox(height: 14),
              TextField(
                controller: _nameController,
                autofocus: true,
                textCapitalization: TextCapitalization.words,
                decoration: const InputDecoration(labelText: 'Nom'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _pinController,
                keyboardType: TextInputType.number,
                maxLength: 4,
                inputFormatters: <TextInputFormatter>[
                  FilteringTextInputFormatter.digitsOnly,
                ],
                decoration: const InputDecoration(
                  labelText: 'PIN (opcional · 4 dígits)',
                  prefixIcon: Icon(Icons.lock_outline_rounded),
                  counterText: '',
                ),
              ),
              const SizedBox(height: 4),
              SwitchListTile.adaptive(
                contentPadding: EdgeInsets.zero,
                value: _active,
                onChanged: (bool v) => setState(() => _active = v),
                title: const Text('Actiu', style: TextStyle(fontWeight: FontWeight.w700)),
                subtitle: const Text('Els inactius no poden iniciar sessió', style: TextStyle(fontSize: 12)),
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
              const SizedBox(height: 14),
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
