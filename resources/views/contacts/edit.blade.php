<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Contactos
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <form action="{{route('contacts.update', $contact)}}" method="post" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <x-validation-errors class="mb-4" />

            <div class="mb-4">
                <x-label for="name" value="Nombre de contacto" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $contact->name)" required autofocus autocomplete="name" placeholder="Ingresar nombre de contacto" />
            </div>

            <div class="mb-4">
                <x-label for="email" value="Correo electrónico" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $contact->user->email)" required autofocus autocomplete="name" placeholder="Ingresar correo electrónico" />
            </div>

            <div class="flex justify-end">
                <x-button class="ml-4">
                    Actualizar Contacto
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>