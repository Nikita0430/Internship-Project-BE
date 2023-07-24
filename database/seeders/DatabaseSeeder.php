<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PasswordResetToken;
use App\Models\Reactor;
use App\Models\ReactorCycle;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private $dogNames = ["Buddy", "Charlie", "Cooper", "Daisy", "Lucy", "Max", "Molly", "Rocky", "Sadie", "Bailey", "Lola", "Luna", "Stella", "Teddy", "Zeus"];
    private $dogBreeds = ["Golden Retriever", "German Shepherd", "Labrador Retriever", "Poodle", "Siberian Husky"];
    private $clinics = [
        [
            'name' => 'Apollo Pet Clinics',
            'email' => 'apollo@mailinator.com',
        ],
        [
            'name' => 'Pet Clinic & Boutique',
            'email' => 'petclinic@mailinator.com',
        ],
        [
            'name' => 'Pet Lovers Veterinary Clinic',
            'email' => 'petlovers@mailinator.com',
        ],
        [
            'name' => 'Cessna Lifeline Veterinary Hospital',
            'email' => 'cessna@mailinator.com',
        ],
        [
            'name' => 'PetZone Veterinary Clinic',
            'email' => 'petzone@mailinator.com',
        ],
        [
            'name' => "Dr. Ajay's Pet Clinic",
            'email' => 'drajay@mailinator.com',
        ],
        [
            'name' => 'Park Veterinary Centre',
            'email' => 'parkvet@mailinator.com',
        ],
        [
            'name' => "Dr. Singhal's Pet Clinic",
            'email' => 'singhalvet@mailinator.com',
        ],
        [
            'name' => 'Prestige Pet Hospital',
            'email' => 'prestigepet@mailinator.com',
        ],
        [
            'name' => 'Animal Health Centre',
            'email' => 'animalhealth@mailinator.com',
        ],
        [
            'name' => 'Vet Life Pet Clinic',
            'email' => 'vetlife@mailinator.com',
        ],
        [
            'name' => 'PetWell Clinic',
            'email' => 'petwell@mailinator.com',
        ],
        [
            'name' => 'Blue Cross Pet Hospital',
            'email' => 'bluecrossvet@mailinator.com',
        ],
        [
            'name' => 'Animal Aid Clinic',
            'email' => 'animalaid@mailinator.com',
        ],
        [
            'name' => 'Best Care Pet Clinic',
            'email' => 'bestcarepet@mailinator.com',
        ],
        [
            'name' => 'PetVet Animal Clinic',
            'email' => 'petvet@mailinator.com',
        ],
        [
            'name' => 'Perfect Pet Clinic',
            'email' => 'perfectpet@mailinator.com',
        ],
        [
            'name' => 'Alpha Pet Clinic',
            'email' => 'alphapet@mailinator.com',
        ],
        [
            'name' => 'PetCare Veterinary Clinic',
            'email' => 'petcarevet@mailinator.com',
        ],
        [
            'name' => 'Sunshine Pet Clinic',
            'email' => 'sunshinepet@mailinator.com',
        ],
        [
            'name' => 'Pet Care and Surgery',
            'email' => 'petcare@mailinator.com',
        ],
        [
            'name' => 'City Pet Care Clinic',
            'email' => 'citypetcare@mailinator.com',
        ],
        [
            'name' => 'Pet Point Veterinary Clinic',
            'email' => 'petpoint@mailinator.com',
        ],
        [
            'name' => 'Skyline Pet Clinic',
            'email' => 'skylinepet@mailinator.com',
        ],
        [
            'name' => 'Vetcare Pet Clinic',
            'email' => 'vetcare@mailinator.com',
        ]
    ];
    private $cities = [
        ["Mumbai", "Maharashtra", "400001"],
        ["Delhi", "Delhi", "110001"],
        ["Kolkata", "West Bengal", "700001"],
        ["Bengaluru", "Karnataka", "560001"],
        ["Chennai", "Tamil Nadu", "600001"],
        ["Hyderabad", "Telangana", "500001"],
        ["Pune", "Maharashtra", "411001"],
        ["Ahmedabad", "Gujarat", "380001"],
        ["Jaipur", "Rajasthan", "302001"],
        ["Lucknow", "Uttar Pradesh", "226001"]
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Factory::create();

        User::updateOrCreate(
            ['email' => 'admin@mailinator.com'],
            [
                'email' => 'admin@mailinator.com',
                'password' => Hash::make('adminpass'),
                'role' => 'admin'
            ]
        );
        PasswordResetToken::firstOrCreate([
            'email' => 'test@mailinator.com',
            'token' => 'testToken'
        ]);

        foreach($this->clinics as $clinic) {
            $user = User::updateOrCreate(
                ['email' => $clinic['email']],
                [
                    'email' => $clinic['email'],
                    'password' => Hash::make(str_replace('@mailinator.com', 'pass', $clinic['email'])),
                    'role' => 'clinic'
                ]
            );

            $randIndex = random_int(0,9);
            Clinic::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $clinic['name'],
                    'is_enabled' => true,
                    'address' => $this->cities[$randIndex][0],
                    'city' => $this->cities[$randIndex][0],
                    'state' => $this->cities[$randIndex][1],
                    'zipcode' => $this->cities[$randIndex][2],
                    'account_id' => 'C'.Str::padLeft($user->id-1,6,'0'),
                    'user_id' => $user->id
                ]
            );
        }

        $reactor1 = Reactor::firstOrCreate([
            'name' => 'Reactor1'
        ]);
        $reactorCycle1 = ReactorCycle::withTrashed()->updateOrCreate(
            ['name' => 'ReactorCycle1'],
            [
                'name' => 'ReactorCycle1',
                'reactor_id' => $reactor1->id,
                'mass' => 30,
                'target_start_date' => Carbon::now()->subDays(5),
                'expiration_date' => Carbon::now()->addDays(5),
            ]
        );
        ReactorCycle::withTrashed()->updateOrCreate(
            ['name' => 'ReactorCycle2'],
            [
                'name' => 'ReactorCycle2',
                'reactor_id' => $reactor1->id,
                'mass' => 50,
                'target_start_date' => Carbon::now()->subDays(15),
                'expiration_date' => Carbon::now()->addDays(6),
            ]
        );
        ReactorCycle::withTrashed()->updateOrCreate(
            ['name' => 'ReactorCycle3'],
            [
                'name' => 'ReactorCycle3',
                'reactor_id' => $reactor1->id,
                'mass' => 60,
                'target_start_date' => Carbon::now()->subDays(2),
                'expiration_date' => Carbon::now()->addDays(9),
            ]
        );
        ReactorCycle::withTrashed()->updateOrCreate(
            ['name' => 'ReactorCycle4'],
            [
                'name' => 'ReactorCycle4',
                'reactor_id' => $reactor1->id,
                'mass' => 0,
                'target_start_date' => Carbon::now()->subDays(5),
                'expiration_date' => Carbon::now()->addDays(5),
            ]
        );

        $reactor2 = Reactor::firstOrCreate([
            'name' => 'Reactor2'
        ]);
        ReactorCycle::withTrashed()->updateOrCreate(
            ['name' => 'ReactorCycle5'],
            [
                'name' => 'ReactorCycle5',
                'reactor_id' => $reactor2->id,
                'mass' => 40,
                'target_start_date' => Carbon::now()->subDays(2),
                'expiration_date' => Carbon::now()->addDays(10),
            ]
        );

        Reactor::firstOrCreate([
            'name' => 'Reactor3'
        ]);

        $reactorPagination = Reactor::firstOrCreate([
            'name' => 'Reactor4'
        ]);
        foreach (range(1,30) as $index){
            ReactorCycle::withTrashed()->updateOrCreate(
                ['name' => 'PaginationReactorCycle'.$index],
                [
                    'name' => 'PaginationReactorCycle'.$index,
                    'reactor_id' => $reactorPagination->id,
                    'mass' => rand(1,20)*10,
                    'target_start_date' => Carbon::now()->subDays(random_int(20,30)),
                    'expiration_date' => Carbon::now()->subDays(15)->addDays(random_int(0,30)),
                    'is_enabled' => false
                ]
            );
        }

        if(Order::count() < 30){
            foreach (range(1,30) as $index){
                $no_of_elbows = random_int(1, 4);
                $dosage_per_elbow = $faker->randomFloat(2, 0.5, 2.5);
                $total_dosage = $no_of_elbows * $dosage_per_elbow;
                $date = Carbon::now()->subDays(random_int(0,5));
    
                $order = Order::create([
                    'clinic_id' => random_int(1,3),
                    'order_no' => 'WEBO0000',
                    'email' => $faker->email(),
                    'placed_at' => $date->toDateTimeString(),
                    'shipped_at' => $date->addDay()->toDateTimeString(),
                    'injection_date' => $date->addDays(random_int(0,4))->toDateTimeString(),
                    'dog_name' => $this->dogNames[random_int(0,14)],
                    'dog_breed' => $this->dogBreeds[random_int(0,4)],
                    'dog_age' => random_int(1,15),
                    'dog_weight' => random_int(5,50),
                    'dog_gender' => $faker->randomElement(['male', 'female']),
                    'no_of_elbows' => $no_of_elbows,
                    'dosage_per_elbow' => $dosage_per_elbow,
                    'total_dosage' => $total_dosage,
                    'reactor_id' => $reactor1->id,
                    'reactor_cycle_id' => $reactorCycle1->id,
                    'status' => $faker->randomElement([config('global.orders.status.Pending')])
                ]);
                $order->order_no = 'WEBO' . sprintf('%04d', $order->id);
                $order->save();
            }
        }
    }
}
