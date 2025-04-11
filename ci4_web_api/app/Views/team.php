<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="tasks-container">
    <h2>Team</h2>
    <p>This is the team section</p>
    <table>
        <tr>
            <td>Team Name</td>
            <td>Description</td>
            <td>Members</td>
        </tr>
    </table>
</div>
<?= $this->endSection() ?>