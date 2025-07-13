<?php

namespace Database\Factories;

use App\Models\Gmap;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GmapFactory extends Factory
{
    protected $model = Gmap::class;

    public function definition()
    {
        return [
            'title'         => $this->faker->text(20),
            'comment'       => $this->faker->text(100),
            'latitude'      => $this->faker->latitude(),
            'longitude'     => $this->faker->longitude(),
            'picture_path'  => 'gmaps/' . $this->faker->lexify('??????????') . '.jpg',
            'magic_word'    => null,
            'user_id'       => User::factory(),
        ];
    }
}
