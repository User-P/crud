<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pruebas para el sistema de registro de usuarios
 *
 * Cubre todos los casos de uso del registro incluyendo validaciones,
 * autorización por roles y manejo de errores.
 */
class UserRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Usuario administrador para tests
     */
    protected User $adminUser;

    /**
     * Setup inicial para cada test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario administrador para tests que requieren autorización
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @test
     * Registro público exitoso de usuario regular
     */
    public function public_user_registration_is_successful(): void
    {
        $userData = [
            'name' => 'Juan Carlos',
            'email' => 'test.user' . rand(1000, 9999) . '@gmail.com',
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_admin',
                    'created_at'
                ],
                'access_token',
                'token_type'
            ]);

        // Verificar que el usuario fue creado en la BD
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'role' => 'user' // Rol por defecto
        ]);

        // Verificar que la contraseña fue hasheada
        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check('TestPassword123!', $user->password));
    }

    /**
     * @test
     * Admin puede crear otro admin
     */
    public function admin_can_create_another_admin(): void
    {
        Sanctum::actingAs($this->adminUser);

        $adminData = [
            'name' => 'Maria Admin',
            'email' => 'admin.test' . rand(1000, 9999) . '@gmail.com',
            'password' => 'AdminPassword123!',
            'password_confirmation' => 'AdminPassword123!',
            'role' => 'admin'
        ];

        $response = $this->postJson('/api/v1/auth/register', $adminData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => $adminData['email'],
            'role' => 'admin'
        ]);
    }

    /**
     * @test
     * Usuario regular no puede crear admin
     */
    public function regular_user_cannot_create_admin(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($regularUser);

        $adminData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'AdminPassword123!',
            'password_confirmation' => 'AdminPassword123!',
            'role' => 'admin'
        ];

        $response = $this->postJson('/api/v1/auth/register', $adminData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
                'error' => ['type' => 'authorization_failed']
            ]);
    }

    /**
     * @test
     * Validación de datos requeridos
     */
    public function registration_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Los datos proporcionados no son válidos',
                'error' => ['type' => 'validation_error']
            ])
            ->assertJsonStructure([
                'error' => ['details' => ['name', 'email', 'password']]
            ]);
    }

    /**
     * @test
     * Validación de formato de email
     */
    public function registration_validates_email_format(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => 'invalid-email',
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonPath('error.details.email', fn($errors) => !empty($errors));
    }

    /**
     * @test
     * Validación de unicidad de email
     */
    public function registration_prevents_duplicate_email(): void
    {
        $existingUser = User::factory()->create();

        $userData = [
            'name' => $this->faker->name,
            'email' => $existingUser->email, // Email duplicado
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonPath('error.details.email', fn($errors) => !empty($errors));
    }

    /**
     * @test
     * Validación de complejidad de contraseña
     */
    public function registration_validates_password_complexity(): void
    {
        $testCases = [
            'short' => '123',
            'no_uppercase' => 'password123!',
            'no_lowercase' => 'PASSWORD123!',
            'no_number' => 'Password!',
            'no_symbol' => 'Password123'
        ];

        foreach ($testCases as $case => $password) {
            $userData = [
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                'password' => $password,
                'password_confirmation' => $password,
            ];

            $response = $this->postJson('/api/v1/auth/register', $userData);

            $response->assertStatus(422)
                ->assertJsonPath(
                    'error.details.password',
                    fn($errors) => !empty($errors),
                    "Password case '{$case}' should fail validation"
                );
        }
    }

    /**
     * @test
     * Validación de confirmación de contraseña
     */
    public function registration_requires_password_confirmation(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'TestPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonPath('error.details.password', fn($errors) => !empty($errors));
    }

    /**
     * @test
     * Validación de caracteres especiales en nombre
     */
    public function registration_validates_name_format(): void
    {
        $userData = [
            'name' => 'Invalid<script>Name',
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonPath('error.details.name', fn($errors) => !empty($errors));
    }

    /**
     * @test
     * Token de acceso se genera correctamente
     */
    public function registration_generates_access_token(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => 'user.test' . rand(1000, 9999) . '@gmail.com',
            'password' => 'TestPassword123!',
            'password_confirmation' => 'TestPassword123!',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure(['access_token', 'token_type']);

        // Verificar que el token funciona
        $token = $response->json('access_token');
        $this->assertNotEmpty($token);

        // Verificar que el token permite acceso al endpoint /me
        $meResponse = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJson(['user' => ['email' => $userData['email']]]);
    }
}
