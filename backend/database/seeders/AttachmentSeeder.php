<?php

namespace Database\Seeders;

use App\Features\Attachments\Models\Attachment;
use App\Features\Edits\Models\Edit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentSeeder extends Seeder
{
    /**
     * Edit title => [file name, description]
     */
    private const ATTACHMENTS = [
        'Update homepage hero images' => [
            ['client-brief.txt', 'What the client asked for'],
            ['feedback-round-1.txt', null],
        ],
        'Design 6 launch posts' => [
            ['post-captions.txt', 'Captions for all six posts'],
        ],
        'Retouch 40 product photos' => [
            ['product-list.csv', 'The 40 products to retouch'],
        ],
        'Translate brochure to French' => [
            ['french-texts.txt', 'Translated texts from the client'],
        ],
        'Charts for the financial section' => [
            ['q4-numbers.csv', 'Numbers for the charts'],
            ['chart-notes.txt', null],
        ],
        'Virtual staging for 3 rooms' => [
            ['room-sizes.txt', null],
        ],
        'Cut a 30-second version' => [
            ['scene-list.txt', 'Scenes to keep in the short version'],
        ],
        'Redesign 20 slides' => [
            ['slide-outline.txt', 'Order and titles of the slides'],
            ['brand-colors.txt', null],
        ],
        'Color grading' => [
            ['final-approval.txt', 'Client approval email'],
        ],
    ];

    public function run(): void
    {
        foreach (self::ATTACHMENTS as $editTitle => $files) {
            $edit = Edit::where('title', $editTitle)->firstOrFail();

            foreach ($files as [$fileName, $description]) {
                $attachment = Attachment::firstOrNew(['edit_id' => $edit->id, 'original_file_name' => $fileName]);

                if ($attachment->exists) {
                    continue;
                }

                // Store a real file, so downloading the attachment works
                $attachment->file_path = 'attachments/seed/'.Str::uuid().'-'.$fileName;
                $attachment->description = $description;
                Storage::disk('local')->put($attachment->file_path, "Sample file \"{$fileName}\" for the edit \"{$editTitle}\".\n");
                $attachment->save();
            }
        }
    }
}
