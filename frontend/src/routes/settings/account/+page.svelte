<script>
import PageHeadline from "$lib/components/layout/PageHeadline.svelte";
import { changePassword } from "$lib/modules/account/api";

let oldPasswort = $state('');
let newPasswort = $state('');
let confirmNewPasswort = $state('');

const saveNewPassword = () => {
    const response = changePassword({
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