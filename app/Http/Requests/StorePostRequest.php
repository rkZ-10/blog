<?php

namespace App\Http\Requests;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Access is enforced by the EnsureLocalEnvironment middleware on the whole admin route group.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', $this->uniqueSlugRule()],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body_markdown' => ['required', 'string'],
            'project_id' => ['nullable', 'integer', Rule::exists(Project::class, 'id')],
            'tags' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'body_markdown' => 'body',
            'project_id' => 'project',
            'published_at' => 'publish date',
        ];
    }

    /**
     * The tag names from the comma-separated "tags" field.
     *
     * @return list<string>
     */
    public function tagNames(): array
    {
        return Str::of($this->validated('tags') ?? '')
            ->explode(',')
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Derive the slug from the title when it is left empty, and normalise whatever was typed.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title', '')),
        ]);
    }

    protected function uniqueSlugRule(): Unique
    {
        return Rule::unique(Post::class, 'slug');
    }
}
