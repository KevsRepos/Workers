<script>
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { fetchApi } from "$lib/fetchApi";

let oldPasswort = $state('');
let newPasswort = $state('');
let confirmNewPasswort = $state('');

const saveNewPassword = () => {
    const response = fetchApi('/account/change-password', 'POST', {
        oldPassword: oldPasswort,
        newPassword: newPasswort,
        confirmNewPassword: confirmNewPasswort,
    });
}
</script>

<PageHeadline>Kontoeinstellungen</PageHeadline>

<main class="mx-4">
    <input bind:value={oldPasswort} class="input mb-2" type="password" placeholder="Altes Passwort"/>

    <input bind:value={newPasswort} class="input mb-2" type="password" placeholder="Neues Passwort" />
    <input bind:value={confirmNewPasswort} class="input mb-2" type="password" placeholder="Neues Passwort bestätigen" />

    <button class="btn preset-filled" onclick={saveNewPassword}>Speichern</button>
</main>