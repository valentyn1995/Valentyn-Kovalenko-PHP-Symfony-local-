<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Profile;

class ProfileControllerTest extends WebTestCase
{
    use BaseTestCase;

    public function testCreateProfileForm(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'profile/profile_form');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('title', 'Profile');
    }

    public function testProfileFormSubmissionSuccess(): void
    {
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('POST', 'profile/profile_form');

        $this->activationProfileForm($client, $crawler, 'Oleg', 'Krat', '22', 'Manager in storage', 'test.png');

        $this->assertResponseRedirects('/profile');

        $client->followRedirect();

        $pageTitle = $crawler->filter('title')->text();
        $this->assertPageTitleSame('Profile info', $pageTitle);

        $this->assertSelectorTextContains('h4', 'Name:', 'Age:');
    }

    /**
     * @dataProvider formValidationLenghtProvider
     */
    public function testProfileFormValidationLenght(string $first_name, string $last_name, string $age, string $biography, string $file, string $expectedType, string $expectedText): void
    {
        $client = $this->createAuthenticatedClient();

        $crawler = $client->request('POST', 'profile/profile_form');

        $this->activationProfileForm($client, $crawler, $first_name, $last_name, $age, $biography, $file);

        $this->assertSelectorTextContains($expectedType, $expectedText);
    }

    public static function formValidationLenghtProvider()
    {
        return [
            ['1234567890abcdegngngngndjdfgdgkl', 'Krat', '22', 'Manager in storage', 'test.png', '.invalid-feedback', 'This value is too long. It should have 25 characters or less.'],
            ['Oleg', 'fffffffffffffffffffffffffffffffffffffKrat', '22', 'Manager in storage', 'test.png', '.invalid-feedback', 'This value is too long. It should have 25 characters or less.'],
            ['Oleg', 'Krat', '2211', 'Manager in storage', 'test.png', '.invalid-feedback', 'This value is too long. It should have 3 characters or less.'],
            ['Oleg', 'Krat', '22', 'I am', 'test.png', '.invalid-feedback', 'This value is too short. It should have 10 characters or more.'],
        ];
    }

    /**
     * @dataProvider fileValidationProvider
     */
    public function testProfileFormValidationInvalidImageTypeAndSize(string $fileName, string $type, string $expectedType, string $expectedText): void
    {
        $client = $this->createAuthenticatedClient();

        $file = $this->createFile($fileName, $type);

        $crawler = $client->request('POST', 'profile/profile_form');

        $this->activationProfileForm($client, $crawler, 'Oleg', 'Krat', '22', 'Manager in storage', $file);

        $this->assertSelectorTextContains($expectedType, $expectedText);
    }

    public static function fileValidationProvider(): array
    {
        return [
            ['PDF_file.pdf', 'application.pdf', '.invalid-feedback', 'The mime type of the file is invalid ("application/pdf"). Allowed mime types are "image/png", "image/jpeg".'],
            ['file_more_than_2mb.jpg', 'image.jpg', '.invalid-feedback', 'The file is too large (2.12 MB). Allowed maximum size is 2 MB.']
        ];
    }

    public function testAddAvatar(): void
    {
        $client = $this->createAuthenticatedClient();

        $file = $this->createFile('file_for_upload.jpg', 'image.jpg');

        $crawler = $client->request('POST', 'profile/profile_form');

        $this->activationProfileForm($client, $crawler, 'Oleg', 'Krat', '22', 'Manager in storage', $file);

        $this->assertResponseRedirects('/profile');
        $crawler = $client->followRedirect();
        $profileRepository = $client->getContainer()->get('doctrine')->getRepository(Profile::class);
        $lastAvatar = $profileRepository->findOneBy([], ['id' => 'DESC']);

        $this->assertCount(1, $crawler->filter("img[src*=\"{$lastAvatar->getAvatarName()}\"]"));
    }

    public function testGetProfileInfo(): void
    {
        $client = $this->createAuthenticatedClientWithProfile();

        $client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.card-body', 'Oleg', 'Krat');
    }

    public function testGetListOfProfiles(): void
    {
        $client = $this->createAuthenticatedClientWithProfile();

        $client->request('GET', '/profile/list_of_profiles');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'List of profiles');
        $this->assertSelectorTextContains('.list-profiles', 'Swiat Step');
        $this->assertSelectorTextNotContains('.list-profiles', 'Oleg Krat');
    }

    public function testViewProfile(): void
    {
        $client = $this->createAuthenticatedClientWithProfile();

        $client->request('GET', '/profile/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', 'Name:', 'Last name:');
        $this->assertSelectorExists('.get-image');
    }
}