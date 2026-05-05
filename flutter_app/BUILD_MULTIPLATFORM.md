# Builds internos multiplataforma

Esta app debe compilarse apuntando a una API Laravel remota accesible por todos los dispositivos.

## API remota

Define siempre la URL con `--dart-define`:

```sh
--dart-define=API_BASE_URL=https://tu-dominio.com/api
```

`AppConfig.assetBaseUrl` deriva las URLs públicas de imágenes quitando el sufijo `/api`, por lo que una API como:

```txt
https://tu-dominio.com/api
```

servirá imágenes desde:

```txt
https://tu-dominio.com/images/...
```

## Android APK interno

```sh
flutter build apk --release --dart-define=API_BASE_URL=https://tu-dominio.com/api
```

Artefacto:

```txt
build/app/outputs/flutter-apk/app-release.apk
```

## iPadOS interno

```sh
flutter build ios --release --dart-define=API_BASE_URL=https://tu-dominio.com/api
```

Aunque Flutter usa el target `ios`, el proyecto está configurado como iPad-only (`TARGETED_DEVICE_FAMILY = 2`) y full screen horizontal. Después abre `ios/Runner.xcworkspace` en Xcode para firmar e instalar en iPad o subir a TestFlight.

## Windows interno

```sh
flutter build windows --release --dart-define=API_BASE_URL=https://tu-dominio.com/api
```

Carpeta a comprimir/distribuir:

```txt
build/windows/x64/runner/Release/
```

## macOS interno

```sh
flutter build macos --release --dart-define=API_BASE_URL=https://tu-dominio.com/api
```

## Linux interno

```sh
flutter build linux --release --dart-define=API_BASE_URL=https://tu-dominio.com/api
```

## Checklist rápido

- La API debe usar HTTPS para iPadOS y builds reales.
- El dominio debe servir `/api/*` y `/images/*`.
- Probar login, catálogo, imágenes, admin, ocultar/mostrar productos y pago antes de entregar cada build.
