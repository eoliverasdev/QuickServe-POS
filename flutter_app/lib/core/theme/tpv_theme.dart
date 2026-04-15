import 'package:flutter/material.dart';

class TpvTheme {
  static const Color primary = Color(0xFF4E73DF);
  static const Color bgBody = Color(0xFFF4F7FE);
  static const Color bgCard = Color(0xFFFFFFFF);
  static const Color textMain = Color(0xFF1B2559);
  static const Color textSecondary = Color(0xFFA3ABB9);
  static const Color danger = Color(0xFFFF4D4D);

  static ThemeData build() {
    final ColorScheme scheme = ColorScheme.fromSeed(
      seedColor: primary,
      primary: primary,
      surface: bgCard,
      onSurface: textMain,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: bgBody,
      textTheme: const TextTheme(
        bodyMedium: TextStyle(color: textMain),
      ),
      cardTheme: CardThemeData(
        color: bgCard,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: const BorderSide(color: Color(0xFFE9EDF7)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFFE9EDF7)),
        ),
      ),
    );
  }
}
