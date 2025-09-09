import 'package:flutter/material.dart';

abstract class BaseCategoryScreen extends StatelessWidget {
  final String title;
  const BaseCategoryScreen(this.title, {super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
        centerTitle: true,
      ),
      body: Center(
        child: Text('Категория: $title',
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w600)),
      ),
    );
  }
}

class VegetablesCategoryScreen extends BaseCategoryScreen {
  const VegetablesCategoryScreen({super.key}) : super('Овощи');
}

class FruitsCategoryScreen extends BaseCategoryScreen {
  const FruitsCategoryScreen({super.key}) : super('Фрукты');
}

class GreensSpicesCategoryScreen extends BaseCategoryScreen {
  const GreensSpicesCategoryScreen({super.key}) : super('Зелень');
}

class DairyCategoryScreen extends BaseCategoryScreen {
  const DairyCategoryScreen({super.key}) : super('Молочные продукты');
}

class BakeryCategoryScreen extends BaseCategoryScreen {
  const BakeryCategoryScreen({super.key}) : super('Лепешкаи и выпечка');
}

class MeatPoultryCategoryScreen extends BaseCategoryScreen {
  const MeatPoultryCategoryScreen({super.key}) : super('Мясо и птица');
}

class FishSeafoodCategoryScreen extends BaseCategoryScreen {
  const FishSeafoodCategoryScreen({super.key}) : super('Рыба и морепродукты');
}

class EggsCategoryScreen extends BaseCategoryScreen {
  const EggsCategoryScreen({super.key}) : super('Яйца');
}

class GrainsPastaLegumesCategoryScreen extends BaseCategoryScreen {
  const GrainsPastaLegumesCategoryScreen({super.key})
      : super('Крупы, макароны и бобовые');
}

class FlourSugarSaltCategoryScreen extends BaseCategoryScreen {
  const FlourSugarSaltCategoryScreen({super.key}) : super('Мука, сахар и соль');
}

class OilsSaucesCategoryScreen extends BaseCategoryScreen {
  const OilsSaucesCategoryScreen({super.key}) : super('Масла и соусы');
}

class DrinksCategoryScreen extends BaseCategoryScreen {
  const DrinksCategoryScreen({super.key}) : super('Напитки');
}

class TeaCoffeeCategoryScreen extends BaseCategoryScreen {
  const TeaCoffeeCategoryScreen({super.key}) : super('Чай и кофе');
}

class CandiesChocolateCategoryScreen extends BaseCategoryScreen {
  const CandiesChocolateCategoryScreen({super.key})
      : super('Конфеты и шоколад');
}

class CannedPreservesCategoryScreen extends BaseCategoryScreen {
  const CannedPreservesCategoryScreen({super.key})
      : super('Консервы и заготовки');
}

class SpicesSeasoningsCategoryScreen extends BaseCategoryScreen {
  const SpicesSeasoningsCategoryScreen({super.key})
      : super('Специи и приправы');
}

class FrozenCategoryScreen extends BaseCategoryScreen {
  const FrozenCategoryScreen({super.key}) : super('Замороженные продукты');
}

class BabyFoodCategoryScreen extends BaseCategoryScreen {
  const BabyFoodCategoryScreen({super.key}) : super('Детское питание');
}

class BreakfastCategoryScreen extends BaseCategoryScreen {
  const BreakfastCategoryScreen({super.key}) : super('Товары для завтрака');
}

class SausagesSemiFinishedCategoryScreen extends BaseCategoryScreen {
  const SausagesSemiFinishedCategoryScreen({super.key})
      : super('Колбасы и полуфабрикаты');
}

class HouseholdChemicalsCategoryScreen extends BaseCategoryScreen {
  const HouseholdChemicalsCategoryScreen({super.key}) : super('Бытовая химия');
}

class PersonalCareCategoryScreen extends BaseCategoryScreen {
  const PersonalCareCategoryScreen({super.key})
      : super('Средства личной гигиены');
}

class PetProductsCategoryScreen extends BaseCategoryScreen {
  const PetProductsCategoryScreen({super.key})
      : super('Корм и товары для животных');
}

class HouseholdGoodsCategoryScreen extends BaseCategoryScreen {
  const HouseholdGoodsCategoryScreen({super.key}) : super('Хозтовары');
}

/// Фабрика по индексу
Widget categoryScreenByIndex(int i) {
  switch (i) {
    case 0:
      return const VegetablesCategoryScreen();
    case 1:
      return const FruitsCategoryScreen();
    case 2:
      return const GreensSpicesCategoryScreen();
    case 3:
      return const DairyCategoryScreen();
    case 4:
      return const BakeryCategoryScreen();
    case 5:
      return const MeatPoultryCategoryScreen();
    case 6:
      return const FishSeafoodCategoryScreen();
    case 7:
      return const EggsCategoryScreen();
    case 8:
      return const GrainsPastaLegumesCategoryScreen();
    case 9:
      return const FlourSugarSaltCategoryScreen();
    case 10:
      return const OilsSaucesCategoryScreen();
    case 11:
      return const DrinksCategoryScreen();
    case 12:
      return const TeaCoffeeCategoryScreen();
    case 13:
      return const CandiesChocolateCategoryScreen();
    case 14:
      return const CannedPreservesCategoryScreen();
    case 15:
      return const SpicesSeasoningsCategoryScreen();
    case 16:
      return const FrozenCategoryScreen();
    case 17:
      return const BabyFoodCategoryScreen();
    case 18:
      return const BreakfastCategoryScreen();
    case 19:
      return const SausagesSemiFinishedCategoryScreen();
    case 20:
      return const HouseholdChemicalsCategoryScreen();
    case 21:
      return const PersonalCareCategoryScreen();
    case 22:
      return const PetProductsCategoryScreen();
    case 23:
      return const HouseholdGoodsCategoryScreen();
    default:
      return const Scaffold(body: Center(child: Text('Категория')));
  }
}
