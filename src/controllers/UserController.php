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
 private UserRepository $userRepository;
    public function __construct(
      
    ) { $this->userRepository = new UserRepository;}

    /**
     * @throws Exception
     */
public function validateUniqueUserData(array $data, ?User $currentUser = null): void
{
    error_log("Validation data: " . json_encode($data));
    error_log("Current user: " . ($currentUser ? $currentUser->getUsername() . " / " . $currentUser->getEmail() : "null"));
    
    $usernameExists = false;
    $emailExists = false;

    // Check username only if it's provided and different from current user's username
    if (!empty($data['username'])) {
        error_log("Checking username: " . $data['username']);
        if ($currentUser === null || $data['username'] !== $currentUser->getUsername()) {
            error_log("Username is different from current, checking database...");
            $existingUser = $this->userRepository->findUserByUsername($data['username']);
            $usernameExists = $existingUser ? true : false;
            error_log("Username exists: " . ($usernameExists ? "yes" : "no"));
        } else {
            error_log("Username same as current user, skipping check");
        }
    }

    // Same for email...
    if (!empty($data['email'])) {
        error_log("Checking email: " . $data['email']);
        if ($currentUser === null || $data['email'] !== $currentUser->getEmail()) {
            error_log("Email is different from current, checking database...");
            $existingUser = $this->userRepository->findUserByEmail($data['email']);
            $emailExists = $existingUser ? true : false;
            error_log("Email exists: " . ($emailExists ? "yes" : "no"));
        } else {
            error_log("Email same as current user, skipping check");
        }
    }

    // Rest of validation...
}

    #[Route('/api/upload-avatar', 'POST')] 
    public function uploadAvatar() {
        
        if (!isset($_FILES['avatar'])) throw new Exception('No file uploaded');
        try { 
            $filename = FileUploadService::handleAvatarUpload($_FILES['avatar'], __DIR__ . '/../../public/uploads/avatar/' ); 
        
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

        
        $user = $this->userRepository->findUserByEmail($data['email']);
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
         $this->validateUniqueUserData($data, $user);
        $user->setCreatedAt((new DateTime())->format('Y-m-d H:i:s'));
        
        $saved = $this->userRepository->save($user);


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

                
                $user = $this->userRepository->findUserByToken($token);
           if (!$user) throw new Exception('User not found ');

            $user->setEmailToken(null);
            $user->setIsVerified(true);

            $updated = $this->userRepository->update($user);
            if (!$updated) throw new Exception('Email verification failed!');
            echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);


        } catch (Exception $e) {
           error_log('Error in UserController::register: ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>  $e->getMessage()]);
        }
    }


     #[Route('/api/user/update', 'POST')]
     public function updateProfil() {
         try {

        $data = json_decode(file_get_contents('php://input'), true );
        if (!$data) throw  new \Exception('Invalid JSON data');
        
        $this->validateUniqueUserData($data);

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        $token = str_replace('Bearer ', '', $authHeader);
        if (!$token) throw new \Exception('Not authorized');

            // Appel du service JWT pour vérifier le token 
        $verifToken = JWTService::verify($token);

         if (!$token) throw new \Exception('Token invalid');

         $user = $this->userRepository->findUserById($verifToken['id_user']);
          if (!$user) throw new \Exception('User not found');
             $this->validateUniqueUserData($data, $user);
          // Mettre à jour les infos utilisateurs 
          if (isset($data['username'])) $user->setUsername($data['username']);
           if (isset($data['email'])) $user->setEmail($data['email']);

          // si autres champs à modifier 
        //   if (isset($data['firstname'])) $user->setFirstname($data['firstname']); // exemple pour autre champs ici on modifie tous les champs modifiable  
        $updated = $this->userRepository->update($user);

        if(!$updated) throw new \Exception('Error during the update of user in the BDD');
        echo json_encode(['success' => true, 'message' => 'Profil up to date' . json_encode($data)]);


     } catch (\Exception $e) {
        error_log('Error updateProfil ' . $e->getMessage());
        http_response_code(400);
        echo json_encode(['success' => false, 'error' =>  $e->getMessage()]);
       }

     }



  #[Route('/api/user/update-avatar', 'POST')]
    /**
     * Met à jour l'avatar de l'utilisateur
     * Route : POST /api/user/update-avatar
     */
    public function updateAvatar(): void
    {
        try {
            // Récupération token
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            $token = str_replace('Bearer ', '', $authHeader);
            if (!$token) throw new \Exception('Not authorized');


            if (!$token) {
                throw new \Exception('Not authorized');
            }

            $payload = JWTService::verify($token);
            if (!$payload) {
                throw new \Exception('Token invalid');
            }

            if (!isset($_FILES['avatar'])) {
                throw new \Exception('No files found');
            }

            
            $user = $this->userRepository->findUserById($payload['id_user']);

            if (!$user) {
                throw new \Exception('User not found');
            }

            // Gérer l'upload de l'avatar
            try {
                $upload_dir = __DIR__ . '/../../public/uploads/avatar/' ;
                 $avatarFilename = FileUploadService::handleAvatarUpload($_FILES['avatar'], $upload_dir  );

                // Supprimer l'ancien avatar si il existe
                if ($user->getAvatar()&& $user->getAvatar() !== 'default_avatar.jpg' ) {
                    FileUploadService::deleteOldAvatar($user->getAvatar(), $upload_dir);
                }

                $user->setAvatar($avatarFilename);
                $updated = $this->userRepository->update($user);

                if (!$updated) {
                    throw new \Exception('Error while updating');
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Avatar up to date with success',
                    'avatar' => $avatarFilename
                ]);
            } catch (\Exception $e) {
                throw new \Exception('Error while uploading ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

}
