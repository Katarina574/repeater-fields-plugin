jQuery(document).ready(function ($) {
    let groupIndex = 0;

    // Dodavanje nove grupe
    $('#add-group').on('click', function () {
        groupIndex++;
        const groupTemplate = `
            <div class="group card mb-4" data-group-index="${groupIndex}">
                <div class="card-header">
                    <input type="text" class="form-control mb-2" name="groups[${groupIndex}][name]" placeholder="Naziv Grupe" required>
                    <select class="form-control mb-2" name="groups[${groupIndex}][post_type]" required>
                        <option value="">Odaberi Post Type</option>
                        <option value="post">Post</option>
                        <option value="page">Page</option>
                    </select>
                    <button type="button" class="btn btn-secondary add-field">Dodaj Polje</button>
                </div>
                <div class="card-body field-container"></div>
            </div>
        `;
        $('#group-container').append(groupTemplate);
    });

    // Dodavanje polja unutar grupe
    $(document).on('click', '.add-field', function () {
        const group = $(this).closest('.group');
        const groupIndex = group.data('group-index');
        const fieldContainer = group.find('.field-container');
        const fieldIndex = fieldContainer.children().length;

        const fieldTemplate = `
            <div class="field mb-3">
                <input type="text" class="form-control mb-2" name="groups[${groupIndex}][fields][${fieldIndex}][name]" placeholder="Ime Polja" required>
                <button type="button" class="btn btn-secondary add-subfield">Dodaj Podpolje</button>
                <div class="subfield-container mt-2"></div>
            </div>
        `;
        fieldContainer.append(fieldTemplate);
    });

    // Dodavanje podpolja unutar polja
    $(document).on('click', '.add-subfield', function () {
        const field = $(this).closest('.field');
        const fieldIndex = field.index();
        const groupIndex = field.closest('.group').data('group-index');
        const subfieldContainer = field.find('.subfield-container');
        const subfieldIndex = subfieldContainer.children().length;

        const subfieldTemplate = `
            <div class="subfield mb-2">
                <input type="text" class="form-control" name="groups[${groupIndex}][fields][${fieldIndex}][subfields][${subfieldIndex}]" placeholder="Ime Podpolja" required>
            </div>
        `;
        subfieldContainer.append(subfieldTemplate);
    });
});
