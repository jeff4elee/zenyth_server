<?php


namespace App\Repositories;

class PasswordResetRepository extends Repository
{
    public function model()
    {
        return 'App\PasswordReset';
    }
}