<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Image;
use App\Tests\Controller\BaseTestCase;

class ImageControllerTest extends WebTestCase
{
    use BaseTestCase;

    public function testSuccessfulImageUpload(): void
    {
        $client = $this->createAuthenticatedClientWithProfile();

        $this->addFile($client, 'file_for_upload.jpg', 'image.jpg');

        $this->assertResponseRedirects('/profile');
        $crawler = $client->followRedirect();

        $imageRepository = $client->getContainer()->get('doctrine')->getRepository(Image::class);
        $lastImage = $imageRepository->findOneBy([], ['id' => 'DESC']);

        $this->assertCount(1, $crawler->filter("img[src*=\"{$lastImage->getImageData()}\"]"));

        $client->request('POST', '/image/remove_image/' . $lastImage->getId());
    }

    public function testRemoveImage(): void
    {
        $client = $this->createAuthenticatedClientWithProfile();

        $this->addFile($client, 'file_for_upload.jpg', 'image.jpg');

        $imageRepository = $client->getContainer()->get('doctrine')->getRepository(Image::class);
        $lastImage = $imageRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertNotNull($imageRepository->find($lastImage->getId()));

        $client->request('POST', '/image/remove_image/' . $lastImage->getId());
        $this->assertResponseRedirects('/profile');
        $this->assertNull($imageRepository->find($lastImage->getId()));
    }

    public function testViewImage(): void
    {
        $client = $this->createAuthenticatedClientWithProfile();

        $this->addFile($client, 'file_for_upload.jpg', 'image.jpg');

        $imageRepository = $client->getContainer()->get('doctrine')->getRepository(Image::class);
        $lastImage = $imageRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertNotNull($imageRepository->find($lastImage->getId()));

        $crawler2 = $client->request('GET', '/image/view_image/' . $lastImage->getId());
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler2->filter("img[src*=\"{$lastImage->getImageData()}\"]"));

        $client->request('POST', '/image/remove_image/' . $lastImage->getId());
    }

    /**
     * @dataProvider formValidationProvider
     */
    public function testProfileFormValidationInvalidImageTypeAndSize(string $fileName, string $type, string $expectedType, string $expectedText): void
    {
        $client = $this->createAuthenticatedClientWithProfile();

        $this->addFile($client, $fileName, $type);

        $this->assertSelectorTextContains($expectedType, $expectedText);
    }

    public static function formValidationProvider(): array
    {
        return [
            ['PDF_file.pdf', 'application.pdf', '.invalid-feedback', 'The mime type of the file is invalid ("application/pdf"). Allowed mime types are "image/png", "image/jpeg".'],
            ['file_more_than_2mb.jpg', 'image.jpg', '.invalid-feedback', 'The file is too large (2.12 MB). Allowed maximum size is 2 MB.']
        ];
    }
}