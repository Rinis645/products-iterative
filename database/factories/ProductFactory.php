<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $catalog = fake()->randomElement([
            ['name' => 'Auzu pārslas 500 g', 'description' => 'Pilngraudu auzu pārslas brokastīm un cepšanai.'],
            ['name' => 'Griķi 1 kg', 'description' => 'Atlasīti griķi sātīgām pusdienām un piedevām.'],
            ['name' => 'Rudzu maize 500 g', 'description' => 'Svaigi cepta rudzu maize ar ieraugu.'],
            ['name' => 'Piena šokolāde 100 g', 'description' => 'Krēmīga piena šokolāde nelielam našķim.'],
            ['name' => 'Maltā kafija 250 g', 'description' => 'Vidēji grauzdēta kafija ar sabalansētu garšu.'],
            ['name' => 'Zaļā tēja 20 maisiņi', 'description' => 'Maiga zaļā tēja ikdienas baudīšanai.'],
            ['name' => 'Rapšu eļļa 1 l', 'description' => 'Rapšu eļļa cepšanai, salātiem un marinādēm.'],
            ['name' => 'Medus 400 g', 'description' => 'Dabīgs ziedu medus no Latvijas pļavām.'],
            ['name' => 'Ābolu sula 1 l', 'description' => 'Atsvaidzinoša sula no Latvijā audzētiem āboliem.'],
            ['name' => 'Tomātu mērce 500 g', 'description' => 'Tomātu mērce pastai un iecienītākajiem ēdieniem.'],
            ['name' => 'Baltās pupiņas 400 g', 'description' => 'Baltās pupiņas zupām, sautējumiem un salātiem.'],
            ['name' => 'Zemeņu ievārījums 300 g', 'description' => 'Salds zemeņu ievārījums brokastīm un desertiem.'],
            ['name' => 'Biezpiens 180 g', 'description' => 'Maigs biezpiens brokastīm vai uzkodām.'],
            ['name' => 'Jogurts bez piedevām 400 g', 'description' => 'Dabīgs jogurts ar maigu, svaigu garšu.'],
            ['name' => 'Sviests 82% 200 g', 'description' => 'Krēmīgs sviests sviestmaizēm un konditorejai.'],
            ['name' => 'Makaroni 500 g', 'description' => 'Kviešu makaroni ātrām un sātīgām maltītēm.'],
            ['name' => 'Jūras sāls 500 g', 'description' => 'Rupjš jūras sāls ēdienu pagatavošanai.'],
            ['name' => 'Mandeļu dzēriens 1 l', 'description' => 'Augu izcelsmes mandeļu dzēriens kafijai un brokastīm.'],
            ['name' => 'Saldētas mellenes 400 g', 'description' => 'Saldētas mellenes smūtijiem un desertiem.'],
            ['name' => 'Vitamīnu komplekss 60 kapsulas', 'description' => 'Ikdienas uztura bagātinātājs pieaugušajiem.'],
        ]);
        $quantity = fake()->numberBetween(0, 200);
        $expirationDate = fake()
            ->optional(0.15)
            ->dateTimeBetween('-6 months', '+2 years')
            ?->format('Y-m-d');

        return [
            'name' => $catalog['name'],
            'quantity' => $quantity,
            'description' => $catalog['description'],
            'expiration_date' => $expirationDate,
            'status' => match (true) {
                $quantity === 0 => 'out_of_stock',
                $expirationDate !== null && $expirationDate < now()->toDateString() => 'expired',
                $quantity < 10 => 'low_stock',
                default => 'available',
            },
        ];
    }
}
