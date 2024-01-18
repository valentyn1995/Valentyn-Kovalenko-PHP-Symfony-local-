<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

class UserControllerTest extends WebTestCase
{
    use BaseTestCase;

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/registration');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Registration form');
    }

    public function testRegistrationFormSubmission(): void
    {
        $client = static::createClient();
        $this->addUser($client, 'testuser04', 'test04@example.com', 'testpass', 'testpass');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', 'A confirmation email has been sent to your email address');
    }

    /**
     * @dataProvider formValidatioProvider
     */
    public function testFormSubmission(string $login, string $email, string $password, string $passwordConfirmation, string $expectedType, string $expectedText): void
    {
        $client = static::createClient();
        $this->addUser($client, $login, $email, $password, $passwordConfirmation);

        $this->assertSelectorTextContains($expectedType, $expectedText);
    }

    public static function formValidatioProvider(): array
    {
        return [
            ['aaa', 'test1@examp.com', 'testpass', 'testpass', '.invalid-feedback', 'This value is too short. It should have 4 characters or more.'],
            ['testuser01', 'testEmail', 'testpass', 'testpass', '.invalid-feedback', 'This value is not a valid email address.'],
            ['testuser02', 'test2@example.com', '12', '12', '.invalid-feedback', 'This value is too short. It should have 6 characters or more.'],
            ['testuser03', 'test3@example.com', '123456', '1234567', '.invalid-feedback', 'The password fields must match.']
        ];
    }

    public function testMailIsSentAndContentOk(): void
    {
        $client = static::createClient();
        $this->addUser($client, 'testuser06', 'test06@example.com', 'testpass', 'testpass');

        $this->assertResponseIsSuccessful();
        $this->assertQueuedEmailCount(1);

        $email = $this->getMailerMessage();
        $this->assertEmailTextBodyContains($email, 'Confirm your email!');
    }

    public function testEmailConfirmation(): void
    {
        $client = static::createClient();
        $this->addUser($client, 'testuser07', 'test07@example.com', 'testpass', 'testpass');

        $userRepository = $client->getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test07@example.com']);
        $token = $user->getToken();

        $client->request('POST', '/confirmation/' . $token);

        $updatedUser = $userRepository->find($user->getId());

        $this->assertTrue($updatedUser->isConfirmed());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-heading', 'Email Confirmation Successful!');
    }

    public function testEmailFailConfirmation(): void
    {
        $client = static::createClient();
        $this->addUser($client, 'testuser', 'test@example.com', 'testpass', 'testpass');

        $client->request('POST', '/confirmation/' . 'testtoken');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-heading', 'Error!');
    }
}
