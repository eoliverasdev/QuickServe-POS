import 'package:flutter/material.dart';

class TpvTheme {
  static const Color primary = Color(0xFF4B6EDB);
  static const Color bgBody = Color(0xFFF2F4FB);
  static const Color bgCard = Color(0xFFFFFFFF);
  static const Color textMain = Color(0xFF1E2A5A);
  static const Color textSecondary = Color(0xFF8790A8);
  static const Color danger = Color(0xFFFF4D4D);

  static ThemeData build() {
    final ColorScheme scheme = ColorScheme.fromSeed(
      seedColor: primary,
      primary: primary,
      surface: bgCard,
      onSurface: textMain,
    );

    const TextTheme baseText = TextTheme(
      headlineLarge: TextStyle(fontSize: 34, fontWeight: FontWeight.w800, color: textMain, letterSpacing: -0.8),
      headlineMedium: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: textMain, letterSpacing: -0.4),
      titleLarge: TextStyle(fontSize: 24, fontWeight: FontWeight.w700, color: textMain),
      titleMedium: TextStyle(fontSize: 20, fontWeight: FontWeight.w700, color: textMain),
      bodyLarge: TextStyle(fontSize: 17, fontWeight: FontWeight.w500, color: textMain),
      bodyMedium: TextStyle(fontSize: 16, fontWeight: FontWeight.w500, color: textMain),
      bodySmall: TextStyle(fontSize: 14, fontWeight: FontWeight.w500, color: textSecondary),
      labelLarge: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: bgBody,
      textTheme: baseText,
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        foregroundColor: textMain,
        titleTextStyle: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: textMain),
      ),
      cardTheme: CardThemeData(
        color: bgCard,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(18),
          side: const BorderSide(color: Color(0xFFE5E9F4)),
        ),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: bgCard,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          textStyle: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
          minimumSize: const Size(0, 50),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: textMain,
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          minimumSize: const Size(0, 46),
          side: const BorderSide(color: Color(0xFFD8DDEA), width: 1.2),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: primary,
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          minimumSize: const Size(0, 44),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xFFF9FAFF),
        hintStyle: const TextStyle(color: Color(0xFF98A2BA)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFDDE2F0)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFDDE2F0)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: primary, width: 1.8),
        ),
      ),
    );
  }
}
