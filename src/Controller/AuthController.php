<?php

namespace App\Controller;

use App\Dto\UserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthController extends AbstractController
{
    #[Route('/api/v1/auth', name: 'api_auth', methods: ['POST'])]
    public function authenticate(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): Response {
        $data = json_decode($request->getContent(), true);

        $dto = new UserDto();
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->fullName = $data['fullName'] ?? '';
        $dto->userName = $data['userName'] ?? '';

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $formattedErrors], Response::HTTP_BAD_REQUEST);
        }

        /** @var User|null $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => $dto->email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $dto->password)) {
            return new JsonResponse([
                'error' => 'Invalid credentials',
                'message' => 'Wrong email or password'
            ], Response::HTTP_UNAUTHORIZED);
        }


        $accessToken = $jwtManager->create($user);

        $refreshPayload = [
            'username' => $user->getUserIdentifier(),
            'exp' => time() + 60 * 60 * 24 * 7
        ];
        $refreshToken = base64_encode(json_encode($refreshPayload));

        $refreshTokenCookie = Cookie::create('refresh_token')
            ->withValue($refreshToken)
            ->withHttpOnly(true)
            ->withSameSite('lax')
            ->withSecure(false)
            ->withPath('/')
            ->withExpires(strtotime('+7 days'));

        $response = new JsonResponse([
            'access_token' => $accessToken,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getFullName(),
                'username' => $user->getUserIdentifier(),
                'role' => $user->getRoles(),
            ]
        ]);
        $response->headers->setCookie($refreshTokenCookie);

        return $response;
    }

    #[Route('/api/v1/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = new UserDto();
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->fullName = $data['fullName'] ?? '';
        $dto->userName = $data['userName'] ?? '';

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $formattedErrors], Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $dto->email]);
        if ($existingUser) {
            return new JsonResponse([
                'error' => 'Email already taken',
                'message' => 'User with this email already exists'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setFullName($dto->fullName);
        $user->setUserName($dto->userName);
        $hashedPassword = $passwordHasher->hashPassword($user, $dto->password);
        $user->setHashPassword($hashedPassword);


        $em->persist($user);
        $em->flush();

        $accessToken = $jwtManager->create($user);

        $refreshPayload = [
            'username' => $user->getEmail(),
            'exp' => time() + 60 * 60 * 24 * 7
        ];
        $refreshToken = base64_encode(json_encode($refreshPayload));

        $refreshTokenCookie = Cookie::create('refresh_token')
            ->withValue($refreshToken)
            ->withHttpOnly(true)
            ->withSameSite('lax')
            ->withSecure(false)
            ->withPath('/')
            ->withExpires(strtotime('+7 days'));

        $response = new JsonResponse([
            'access_token' => $accessToken,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getFullName(),
                'username' => $user->getUserIdentifier(),
                'role' => $user->getRoles()
            ]
        ], Response::HTTP_CREATED);
        $response->headers->setCookie($refreshTokenCookie);

        return $response;
    }

    #[Route('/api/v1/refresh-token', name: 'api_refresh_token', methods: ['GET'])]
    public function refreshToken(
        Request $request,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {

        $refreshToken = $request->cookies->get('refresh_token');

      //  dd($request->cookies->all());
        if (!$refreshToken) {
            return new JsonResponse([
                'error' => 'Missing refresh token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $refreshPayload = json_decode(base64_decode($refreshToken), true);

        if (!$refreshPayload || ($refreshPayload['exp'] ?? 0) < time()) {
            return new JsonResponse([
                'error' => 'Invalid or expired refresh token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $em->getRepository(User::class)->findOneBy(['username' => $refreshPayload['username'] ?? null]);

        if (!$user) {
            return new JsonResponse([
                'error' => 'User not found'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $accessToken = $jwtManager->create($user);

        return new JsonResponse([
            'access_token' => $accessToken,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getFullName(),
                'username' => $user->getUserIdentifier(),
                'role' => $user->getRoles()
            ]
        ]);
    }

    #[Route('/api/v1/logout', name: 'api_logout', methods: ['GET'])]
    public function logout(): JsonResponse
    {
        // Удаляем куку (устанавливаем с истекшим сроком)
        $response = new JsonResponse(['message' => 'Logged out']);
        $expiredCookie = Cookie::create('refresh_token')
            ->withValue('')
            ->withExpires(time() - 3600)
            ->withHttpOnly(true)
            ->withPath('/')
            ->withSameSite('Strict');

        $response->headers->setCookie($expiredCookie);
        return $response;
    }

}
