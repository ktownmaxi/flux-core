<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaTest extends PortalSetup
{
    private string $filename;

    private Media $media;

    protected function setUp(): void
    {
        parent::setUp();

        $file = UploadedFile::fake()->image('TestFile.png');

        $this->media = $this->user->contact->addMedia($file)
            ->usingFileName($this->filename = Str::random() . '.png')
            ->toMediaCollection();
    }

    public function test_download_media(): void
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('media.{media}.{filename}.get', 'address')
        );

        $this->actingAs($this->user, 'address')
            ->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(200)
            ->assertDownload();
    }

    public function test_download_media_media_not_found(): void
    {
        $this->media->delete();

        $this->user->givePermissionTo(
            Permission::findOrCreate('media.{media}.{filename}.get', 'address')
        );

        $this->actingAs($this->user, 'address')
            ->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(404);
    }

    public function test_download_media_no_user(): void
    {
        $this->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_download_media_without_permission(): void
    {
        Permission::findOrCreate('media.{media}.{filename}.get', 'address');

        $this->actingAs($this->user, 'address')
            ->get($this->portalDomain . '/media/' . $this->media->id . '/' . $this->filename)
            ->assertStatus(403);
    }
}
