/// Layout helpers for TPV on smaller tablets (e.g. 10" Android) vs large iPad.
abstract final class TpvResponsive {
  TpvResponsive._();

  /// Typography / padding scale when the **content** area is narrow (< 800 logical px).
  static double contentScale(double width) {
    if (width < 720) return 0.88;
    if (width < 800) return 0.92;
    if (width < 1000) return 0.96;
    return 1.0;
  }

  /// Product grid columns from **available grid width** (middle panel), with
  /// an escape hatch for very wide devices (e.g. iPad 13") to keep 4 columns.
  static int productGridCrossAxisCount(
    double gridInnerWidth, {
    double? screenWidth,
  }) {
    // Keep 4 columns on large iPad layouts even if side panels reduce grid width.
    if (screenWidth != null && screenWidth >= 1200 && gridInnerWidth >= 760) {
      return 4;
    }
    if (gridInnerWidth >= 1400) return 5;
    if (gridInnerWidth >= 1080) return 4;
    if (gridInnerWidth >= 760) return 3;
    return 2;
  }

  /// Taller tiles when cells are narrow (avoid squashed cards).
  static double productGridAspectRatio(double gridInnerWidth, int columns) {
    final double cellW = gridInnerWidth / columns;
    if (cellW < 150) return 0.66;
    if (cellW < 175) return 0.72;
    if (gridInnerWidth < 800) return 0.76;
    if (gridInnerWidth < 1100) return 0.82;
    return 0.88;
  }

  static double gridSpacing(double gridInnerWidth) =>
      gridInnerWidth < 800 ? 8 : 12;

  static double ticketPanelWidth(double screenWidth) =>
      (screenWidth * 0.255).clamp(280.0, 430.0);

  static double sideBarWidth(double screenWidth) =>
      screenWidth < 720 ? 72.0 : 90.0;

  static double horizontalGap(double screenWidth) =>
      screenWidth < 800 ? 8.0 : 12.0;

  static double screenEdgePadding(double screenWidth) =>
      screenWidth < 800 ? 10.0 : 14.0;

  static double cardPanelPadding(double screenWidth) =>
      screenWidth < 800 ? 12.0 : 16.0;
}
