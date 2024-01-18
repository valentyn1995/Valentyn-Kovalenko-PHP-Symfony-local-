<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

trait BaseTestCase
{
    public function createAuthenticatedClientWithProfile(): KernelBrowser
    {
        $client = static::createClient();
        $this->authentication($client);
        $this->addProfile($client);

        return $client;
    }

    public function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $this->authentication($client);

        return $client;
    }

    public function addUser(KernelBrowser $client, string $login, string $email, string $password, string $passwordConfirmation): void
    {
        $crawler = $client->request('GET', '/registration');

        $form = $crawler->selectButton('Submit')->form();
        $form['register_form[login]'] = $login;
        $form['register_form[email]'] = $email;
        $form['register_form[password][first]'] = $password;
        $form['register_form[password][second]'] = $passwordConfirmation;

        $client->submit($form);
    }

    public function activationProfileForm(KernelBrowser $client, Crawler $crawler, string $first_name, string $last_name, string $age, string $biography, string $file): void
    {
        $formProfile = $crawler->selectButton('Save')->form();
        $formProfile['profile_form[first_name]'] = $first_name;
        $formProfile['profile_form[last_name]'] = $last_name;
        $formProfile['profile_form[age]'] = $age;
        $formProfile['profile_form[biography]'] = $biography;
        $formProfile['profile_form[avatar_name]'] = $file;

        $client->submit($formProfile);
    }

    public function activationImageForm(Crawler $crawler, KernelBrowser $client, UploadedFile $uploadedFile): void
    {
        $form = $crawler->selectButton('Save')->form();
        $form['add_image[image]'] = $uploadedFile;

        $client->submit($form);
    }

    public function createFile(string $fileName, string $type): UploadedFile
    {
        $imagePath = __DIR__ . '/../files_for_tests/' . $fileName;
        $uploadedFile = new UploadedFile(
            $imagePath,
            $fileName,
            $type,
            null,
            true
        );

        return $uploadedFile;
    }

    public function addFile(KernelBrowser $client, $fileName, $fileType): void
    {
        $crawler = $client->request('GET', '/image/add_image');

        $uploadedFile = $this->createFile($fileName, $fileType);

        $this->activationImageForm($crawler, $client, $uploadedFile);
    }

    private function authentication(KernelBrowser $client): void
    {
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form();
        $form['login'] = 'testuser';
        $form['password'] = '123456';

        $client->submit($form);
    }

    private function addProfile(KernelBrowser $client): void
    {
        $crawler = $client->request('POST', 'profile/profile_form');

        $formProfile = $crawler->selectButton('Save')->form();
        $formProfile['profile_form[first_name]'] = 'Oleg';
        $formProfile['profile_form[last_name]'] = 'Krat';
        $formProfile['profile_form[age]'] = '22';
        $formProfile['profile_form[biography]'] = 'Manager in storage';
        $formProfile['profile_form[avatar_name]'] = 'test.png';

        $client->submit($formProfile);
    }
}