class AppConfig {
  // For Android emulator use 10.0.2.2 instead of localhost.
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8000/api',
  );

  /// Base URL used for public assets (images, uploads...). Built by stripping
  /// the trailing "/api" from [apiBaseUrl].
  static String get assetBaseUrl {
    const String suffix = '/api';
    if (apiBaseUrl.endsWith(suffix)) {
      return apiBaseUrl.substring(0, apiBaseUrl.length - suffix.length);
    }
    return apiBaseUrl;
  }

  /// Turns a relative image path (e.g. "images/productes/foo.jpg") or an
  /// absolute URL into a fully qualified URL the client can load.
  static String resolveAsset(String pathOrUrl) {
    final String trimmed = pathOrUrl.trim();
    if (trimmed.isEmpty) return trimmed;
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return trimmed;
    }
    final String base = assetBaseUrl.endsWith('/')
        ? assetBaseUrl.substring(0, assetBaseUrl.length - 1)
        : assetBaseUrl;
    final String rel = trimmed.startsWith('/') ? trimmed.substring(1) : trimmed;
    return '$base/$rel';
  }
}
