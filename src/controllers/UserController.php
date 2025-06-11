<?php

declare(strict_types=1);
namespace App\controllers;
use Exception;
use App\models\User;
use App\core\attributes\Route;
use App\repository\UserRepository;
use DateTime;
use App\services\FileUploadService;

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

             $userRepository = new UserRepository();
        $saved = $userRepository->saveAvatar($filename);
         if(!$saved) throw new Exception('User avatar upload failed!');



            echo json_encode([
                'success' => true,
                'message' => 'File uploaded successfully',
                'filename' => $filename
            ]);
        } catch (Exception $e) {
            
            throw new Exception('File upload failed: ' . $e->getMessage());
            
        }
    }



    #[Route('/api/register', 'POST')]
    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true );
        if (!$data) throw  new \Exception('Invalid JSON data');


        $userData = [
            'username' => $data['username'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => password_hash($data['password'], PASSWORD_BCRYPT) ?? '',
            // 'avatar' => $data['avatar'] ?? 'default_avatar.png',
            // 'role' => $data['role'] ?? ['ROLE_USER'],
            // 'created_at' => new \DateTime(),
        ];


        //création user 
        $user = new User($userData);
        $user->setCreatedAt((new DateTime())->format('Y-m-d H:i:s'));
        $userRepository = new UserRepository();
        $saved = $userRepository->save($user);


        if(!$saved) throw new Exception('User registration failed!');
 


       echo json_encode(['success' => true, 'message' => 'User registered successfully!' . json_encode($data)]);
    }
}
