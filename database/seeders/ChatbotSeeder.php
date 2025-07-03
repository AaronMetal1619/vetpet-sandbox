<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatbotQuestion;

class ChatbotSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'question' => '¿Cuál es el horario de atención?',
                'answer' => 'Nuestro horario es de lunes a sábado, de 9:00 a.m. a 7:00 p.m.'
            ],
            [
                'question' => '¿Atienden urgencias?',
                'answer' => 'Sí, contamos con atención a urgencias las 24 horas.'
            ],
            [
                'question' => '¿Dónde están ubicados?',
                'answer' => 'Nuestra clínica se encuentra en Av. Principal 123, Colonia Centro.'
            ],
            [
                'question' => '¿Qué servicios ofrecen?',
                'answer' => 'Ofrecemos consultas, vacunación, desparasitación, cirugías, estética y tienda de mascotas.'
            ],
            [
                'question' => '¿Realizan esterilizaciones?',
                'answer' => 'Sí, realizamos esterilizaciones con previa cita.'
            ],
            [
                'question' => '¿Atienden a gatos y perros?',
                'answer' => 'Sí, atendemos tanto a gatos como a perros.'
            ],
            [
                'question' => '¿Venden alimento para mascotas?',
                'answer' => 'Sí, contamos con una tienda donde vendemos alimento y productos veterinarios.'
            ],
            [
                'question' => '¿Cómo puedo agendar una cita?',
                'answer' => 'Puedes agendar tu cita llamando al 555-123-4567 o por WhatsApp.'
            ],
            [
                'question' => '¿Cuánto cuesta una consulta?',
                'answer' => 'El costo de la consulta general es de $300 MXN.'
            ],
            [
                'question' => '¿Aceptan pagos con tarjeta?',
                'answer' => 'Sí, aceptamos pagos en efectivo, tarjeta de crédito y débito.'
            ],
        ];

        foreach ($questions as $q) {
            ChatbotQuestion::create($q);
        }
    }
}
