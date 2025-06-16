<?php

declare(strict_types=1);
namespace App\controllers;
use Exception;
use App\models\User;
use App\core\attributes\Route;
use App\repository\UserRepository;
use DateTime;
use App\services\FileUploadService;
use App\services\JWTService;
use App\services\MailService;

class UserController
{

    #[Route('/api/upload-avatar', 'POST')] 
    public function uploadAvatar() {
        
        if (!isset($_FILES['avatar'])) throw new Exception('No file uploaded');
        try { 
            $filename = FileUploadService::handleAvatarUpload($_FILES['avatar'], __DIR__ . '/../../public/uploads/avatar/' ); 
            // if ($user->getAvatar() !== 'default_avatar.png') {
            //     FileUploadService::deleteOldAvatar($user->getAvatar());
            // }




            echo json_encode([
                'success' => true,
                'message' => 'File uploaded successfully',
                'filename' => $filename
            ]);
        } catch (Exception $e) {
            
            throw new Exception('File upload failed: ' . $e->getMessage());
            
        }
    }


       #[Route('/api/login', 'POST')] 
       public function login()
       {
        try {
        $data = json_decode(file_get_contents('php://input'), true );
        if (!$data) throw  new \Exception('Invalid JSON data');

        $userRepository = new UserRepository();
        $user = $userRepository->findUserByEmail($data['email']);
        if(!$user) throw new \Exception('Email or password incorrect !');
        if (!password_verify($data['password'], $user->getPassword())) throw new \Exception('Email or password is incorrect');
        if(!$user->getIsVerified()) throw new \Exception('Please make sure your email is verified before trying to log in');

            //génerer le token  JWT
            $token = JWTService::generate([
                "id_user" => $user->getId(),
                "role" => $user->getRole(),
                "email" =>$user->getEmail()
            ]);


        


        echo json_encode([

            'success' => true, 
            'token' => $token,
            'user' => [
            'avatar' => $user->getAvatar(),
            'username' => $user->getUsername()

            ]
        ]);

        } catch (\Exception $e) {
        error_log('Error in UserController::login: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>  $e->getMessage()]);
       }

       }


    #[Route('/api/register', 'POST')]
    public function register()
    {
        try {
        $data = json_decode(file_get_contents('php://input'), true );
        if (!$data) throw  new \Exception('Invalid JSON data');

        $userRepository = new UserRepository;


             if ($userRepository->findUserByUsername($data['username']) && $userRepository->findUserByEmail($data['email'])) {
                throw new Exception("An account has already been created with this username and this adress email .");
            } elseif ($userRepository->findUserByEmail($data['email'])) {
                throw new Exception('This Email is already Used !');
            } elseif ($userRepository->findUserByUsername($data['username'])) {
                throw new Exception("This username is alreasy Used !");
            };
        

        $emailToken = bin2hex(random_bytes(32));


        $userData = [
            'username' => $data['username'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => password_hash($data['password'], PASSWORD_BCRYPT) ?? '',
            'avatar' => $data['avatar'] ?? 'default_avatar.png',
            'email_token' => $emailToken,
            // 'role' => $data['role'] ?? ['ROLE_USER'],
            // 'created_at' => new \DateTime(),
        ];



        //création user 
        $user = new User($userData);
        $user->setCreatedAt((new DateTime())->format('Y-m-d H:i:s'));
        
        $saved = $userRepository->save($user);


        if(!$saved) throw new Exception('User registration failed!');
 
        if(!$user->getEmailToken()) throw new Exception('Email token generation failed!');

        MailService::sendVerificationEmail(
            $user->getEmail(),
            $user->getEmailToken()
        );

       echo json_encode(['success' => true, 'message' => 'User registered successfully!' . json_encode($data)]);
       } catch (\Exception $e) {
        error_log('Error in UserController::register: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>  $e->getMessage()]);
       }
    }

    #[Route('/api/verify-email')]
    public function verifyEmail()
    {
        try {
           $token = $_GET['token'] ?? null;
           if (!$token) 
                throw new Exception('Token is required for email verification');

                $userRepository = new UserRepository();
                $user = $userRepository->findUserByToken($token);
           if (!$user) throw new Exception('User not found ');

            $user->setEmailToken(null);
            $user->setIsVerified(true);

            $updated = $userRepository->update($user);
            if (!$updated) throw new Exception('Email verification failed!');
            echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);


        } catch (Exception $e) {
           error_log('Error in UserController::register: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>  $e->getMessage()]);
        }
    }
}
