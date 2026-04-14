// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_app/main.dart';

void main() {
  testWidgets('Shows API health check screen', (WidgetTester tester) async {
    await tester.pumpWidget(const MyApp(enableHealthCheck: false));

    expect(find.text('Quickserve API check'), findsOneWidget);
    expect(find.text('Reintentar'), findsOneWidget);

    await tester.pump();
  });
}
