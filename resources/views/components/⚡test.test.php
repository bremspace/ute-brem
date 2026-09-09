<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('test')
        ->assertStatus(200);
});
