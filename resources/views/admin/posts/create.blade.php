<x-admin.layout title="New post">
    <form method="POST" action="{{ route('admin.posts.store') }}">
        @include('admin.posts._form', ['submitLabel' => 'Create post'])
    </form>
</x-admin.layout>
