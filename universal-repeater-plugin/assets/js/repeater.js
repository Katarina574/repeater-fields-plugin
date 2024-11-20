document.addEventListener("DOMContentLoaded", function () {
    const groupsWrapper = document.getElementById("groups-wrapper");

    // Dodavanje nove grupe
    document.getElementById("add-group").addEventListener("click", function () {
        let groupIndex = groupsWrapper.querySelectorAll(".group-item").length;

        let groupHTML = `
            <div class="group-item border p-3 mb-4">
                <h2>Group ${groupIndex + 1}</h2>
                <input type="text" class="form-control mb-2" name="groups[${groupIndex}][name]" placeholder="Group Name">
                <div class="fields-wrapper"></div>
                <button class="btn btn-secondary add-field" data-group-index="${groupIndex}" type="button">Add Field</button>
                <button class="btn btn-danger remove-group mt-2" type="button">Remove Group</button>
            </div>`;

        groupsWrapper.insertAdjacentHTML("beforeend", groupHTML);
    });

    // Event delegation za dinamičke događaje
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-field")) {
            let groupIndex = e.target.getAttribute("data-group-index");
            let fieldsWrapper = e.target.closest(".group-item").querySelector(".fields-wrapper");
            let fieldIndex = fieldsWrapper.querySelectorAll(".field-row").length;

            let fieldHTML = `
                <div class="field-row mb-3">
                    <input type="text" class="form-control mb-2" name="groups[${groupIndex}][fields][${fieldIndex}][label]" placeholder="Field Label">
                    <input type="text" class="form-control mb-2" name="groups[${groupIndex}][fields][${fieldIndex}][value]" placeholder="Value">
                    <button class="btn btn-danger remove-field mt-1" type="button">Remove Field</button>
                </div>`;

            fieldsWrapper.insertAdjacentHTML("beforeend", fieldHTML);
        }

        if (e.target.classList.contains("remove-field")) {
            e.target.closest(".field-row").remove();
        }

        if (e.target.classList.contains("remove-group")) {
            e.target.closest(".group-item").remove();
        }
    });
});
