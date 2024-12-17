<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;

class AuthApiFeatureTest extends TestCase
{
    // testando a necessidade de autenticação para o endpoint de categories
    public function testAuthCategories()
    {
        // fazendo o request e avaliando o status da resposta
        $this->getJson('/api/categories')->assertStatus(401);
        $this->getJson('/api/categories/fake')->assertStatus(401);
        $this->postJson('/api/categories')->assertStatus(401);
        $this->putJson('/api/categories/fake')->assertStatus(401);
        $this->deleteJson('/api/categories/fake')->assertStatus(401);        
    }

    // testando a necessidade de autenticação para o endpoint de genres
    public function testAuthGenres()
    {
        // fazendo o request e avaliando o status da resposta
        $this->getJson('/api/genres')->assertStatus(401);
        $this->getJson('/api/genres/fake')->assertStatus(401);
        $this->postJson('/api/genres')->assertStatus(401);
        $this->putJson('/api/genres/fake')->assertStatus(401);
        $this->deleteJson('/api/genres/fake')->assertStatus(401);        
    }

    // testando a necessidade de autenticação para o endpoint de cast members
    public function testAuthCastMembers()
    {
        // fazendo o request e avaliando o status da resposta
        $this->getJson('/api/cast_members')->assertStatus(401);
        $this->getJson('/api/cast_members/fake')->assertStatus(401);
        $this->postJson('/api/cast_members')->assertStatus(401);
        $this->putJson('/api/cast_members/fake')->assertStatus(401);
        $this->deleteJson('/api/cast_members/fake')->assertStatus(401);        
    }

    // testando a necessidade de autenticação para o endpoint de videos
    public function testAuthVideos()
    {
        // fazendo o request e avaliando o status da resposta
        $this->getJson('/api/videos')->assertStatus(401);
        $this->getJson('/api/videos/fake')->assertStatus(401);
        $this->postJson('/api/videos')->assertStatus(401);
        $this->putJson('/api/videos/fake')->assertStatus(401);
        $this->deleteJson('/api/videos/fake')->assertStatus(401);        
    }
}
