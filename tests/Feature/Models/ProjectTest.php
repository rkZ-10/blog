<?php

use App\Models\Project;
use Illuminate\Support\Facades\DB;

it('stores the stack as a json array and reads it back as a php array', function () {
    $project = Project::factory()->create(['stack' => ['MCP', 'Hybrid RAG']]);

    $storedValue = DB::table('projects')->where('id', $project->id)->value('stack');

    expect(json_decode($storedValue, true))->toBe(['MCP', 'Hybrid RAG']);
    expect($project->fresh()->stack)->toBe(['MCP', 'Hybrid RAG']);
});

it('keeps a missing stack as null', function () {
    $project = Project::factory()->create(['stack' => null]);

    expect(DB::table('projects')->where('id', $project->id)->value('stack'))->toBeNull();
    expect($project->fresh()->stack)->toBeNull();
});
