<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Member;
use App\Models\Paroisse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paroisse = Paroisse::first();

        if (! $paroisse) {
            $this->command?->warn('Aucune paroisse trouvée, EventSeeder ignoré.');
            return;
        }

        $celebrant = Member::query()
            ->where('paroisse_id', $paroisse->id)
            ->where(function ($q): void {
                $q->where('notes', 'like', '%curé%')
                    ->orWhere('notes', 'like', '%abbé%')
                    ->orWhere('notes', 'like', '%père%')
                    ->orWhere('notes', 'like', '%pere%');
            })
            ->first();

        $baseDate = now()->startOfDay()->addDays(1);

        $events = [
            [
                'titre' => 'Messe dominicale',
                'type' => 'messe',
                'date_evenement' => $baseDate->copy(),
                'heure_evenement' => $baseDate->copy()->setTime(9, 0),
                'lieu' => 'Église principale',
                'intention' => 'Pour la paroisse',
            ],
            [
                'titre' => 'Veillée de prière',
                'type' => 'célébration',
                'date_evenement' => $baseDate->copy()->addDays(3),
                'heure_evenement' => $baseDate->copy()->setTime(18, 30),
                'lieu' => 'Chapelle Saint Joseph',
                'intention' => null,
            ],
            [
                'titre' => 'Réunion du conseil paroissial',
                'type' => 'activité',
                'date_evenement' => $baseDate->copy()->addDays(7),
                'heure_evenement' => $baseDate->copy()->setTime(19, 0),
                'lieu' => 'Salle paroissiale',
                'intention' => null,
            ],
        ];

        foreach ($events as $data) {
            Event::create([
                'titre' => $data['titre'],
                'type' => $data['type'],
                'date_evenement' => $data['date_evenement'],
                'heure_evenement' => $data['heure_evenement'],
                'lieu' => $data['lieu'],
                'celebre_par_id' => $celebrant?->id,
                'intention' => $data['intention'],
                'description' => null,
                'paroisse_id' => $paroisse->id,
            ]);
        }

        $this->command?->info('Événements de test créés avec succès (EventSeeder).');
    }
}
