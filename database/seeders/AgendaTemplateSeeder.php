<?php

namespace Database\Seeders;

use App\Models\AgendaTemplate;
use Illuminate\Database\Seeder;

class AgendaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Membaca data dari file JSON config
        $templates = config('templates.agenda_templates.templates', []);
        
        if (empty($templates)) {
            $this->command->warn('Tidak ada data template ditemukan di config/templates/agenda_templates.json');
            return;
        }

        foreach ($templates as $templateData) {
            AgendaTemplate::updateOrCreate(
                ['code' => $templateData['code']], // Kondisi pencarian (agar tidak duplikat)
                [
                    'name' => $templateData['name'],
                    'icon' => $templateData['icon'],
                    'description' => $templateData['description'],
                    'requires_room' => $templateData['requires_room'],
                    'requires_letter' => $templateData['requires_letter'],
                    'letter_template' => $templateData['pdf_template'],
                    'form_schema' => $templateData['form_schema'],
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
        }

        $this->command->info('Template Agenda berhasil di-seed ke database!');
    }
}