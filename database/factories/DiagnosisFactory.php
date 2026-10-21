<?php

namespace Database\Factories;

use App\Enums\DiagnosisStatus;
use App\Models\Diagnosis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Diagnosis>
 */
class DiagnosisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->producer(),
            'image_path' => 'diagnoses/'.Str::uuid().'.jpg',
            'crop' => 'cacao',
            'result' => [
                'culture' => 'cacao',
                'symptomes' => ['Taches brunes sur les cabosses'],
                'hypotheses' => [['probleme' => 'Pourriture brune', 'confiance' => 0.82]],
                'actions_immediates' => ['Retirer et enfouir les cabosses atteintes'],
                'prevention' => ['Récolter régulièrement', 'Aérer la plantation'],
                'consulter_technicien' => true,
                'image_exploitable' => true,
            ],
            'confidence' => 0.82,
            'status' => DiagnosisStatus::Completed,
        ];
    }
}
