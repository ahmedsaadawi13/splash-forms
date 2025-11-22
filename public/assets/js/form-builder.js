// FILE: /public/assets/js/form-builder.js

let formSchema = [];
let selectedFieldIndex = null;

document.addEventListener('DOMContentLoaded', function() {
    const schemaInput = document.getElementById('form-schema');
    if (schemaInput && schemaInput.value) {
        formSchema = JSON.parse(schemaInput.value);
        renderFormCanvas();
    }

    const fieldTypes = document.querySelectorAll('.field-type');
    const canvas = document.getElementById('form-canvas');

    fieldTypes.forEach(type => {
        type.addEventListener('click', function() {
            addField(this.getAttribute('data-type'));
        });
    });

    const saveButton = document.getElementById('save-form');
    if (saveButton) {
        saveButton.addEventListener('click', saveForm);
    }
});

function addField(type) {
    const field = {
        type: type,
        name: 'field_' + Date.now(),
        label: type.charAt(0).toUpperCase() + type.slice(1),
        placeholder: '',
        required: false,
        options: type === 'select' || type === 'radio' ? ['Option 1', 'Option 2'] : undefined
    };

    formSchema.push(field);
    renderFormCanvas();
}

function renderFormCanvas() {
    const canvas = document.getElementById('form-canvas');
    canvas.innerHTML = '';

    if (formSchema.length === 0) {
        canvas.innerHTML = '<p class="empty-message">Drag fields here to build your form</p>';
        return;
    }

    formSchema.forEach((field, index) => {
        const fieldElement = createFieldElement(field, index);
        canvas.appendChild(fieldElement);
    });
}

function createFieldElement(field, index) {
    const div = document.createElement('div');
    div.className = 'form-field-item';
    div.style.cssText = 'padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; margin-bottom: 0.5rem; cursor: pointer;';

    div.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>${field.label}</strong>
                <span style="color: #6b7280; font-size: 0.875rem;"> (${field.type})</span>
                ${field.required ? '<span style="color: #ef4444;">*</span>' : ''}
            </div>
            <div>
                <button class="btn btn-sm" onclick="editField(${index}); event.stopPropagation();">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteField(${index}); event.stopPropagation();">Delete</button>
            </div>
        </div>
    `;

    div.onclick = () => editField(index);

    return div;
}

function editField(index) {
    selectedFieldIndex = index;
    const field = formSchema[index];

    const propertiesDiv = document.getElementById('field-properties');
    const contentDiv = document.getElementById('properties-content');

    let optionsHtml = '';
    if (field.type === 'select' || field.type === 'radio') {
        const options = field.options || [];
        optionsHtml = `
            <div class="form-group">
                <label>Options (one per line)</label>
                <textarea id="field-options" class="form-control" rows="4">${options.join('\n')}</textarea>
            </div>
        `;
    }

    contentDiv.innerHTML = `
        <div class="form-group">
            <label>Field Name</label>
            <input type="text" id="field-name" class="form-control" value="${field.name}">
        </div>
        <div class="form-group">
            <label>Label</label>
            <input type="text" id="field-label" class="form-control" value="${field.label}">
        </div>
        <div class="form-group">
            <label>Placeholder</label>
            <input type="text" id="field-placeholder" class="form-control" value="${field.placeholder || ''}">
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" id="field-required" ${field.required ? 'checked' : ''}>
                Required
            </label>
        </div>
        ${optionsHtml}
        <button class="btn btn-primary" onclick="saveFieldProperties()">Save</button>
        <button class="btn" onclick="closeProperties()">Cancel</button>
    `;

    propertiesDiv.style.display = 'block';
}

function saveFieldProperties() {
    if (selectedFieldIndex === null) return;

    const field = formSchema[selectedFieldIndex];

    field.name = document.getElementById('field-name').value;
    field.label = document.getElementById('field-label').value;
    field.placeholder = document.getElementById('field-placeholder').value;
    field.required = document.getElementById('field-required').checked;

    const optionsTextarea = document.getElementById('field-options');
    if (optionsTextarea) {
        const optionsText = optionsTextarea.value;
        field.options = optionsText.split('\n').filter(o => o.trim() !== '');
    }

    renderFormCanvas();
    closeProperties();
}

function closeProperties() {
    document.getElementById('field-properties').style.display = 'none';
    selectedFieldIndex = null;
}

function deleteField(index) {
    if (confirm('Delete this field?')) {
        formSchema.splice(index, 1);
        renderFormCanvas();
        closeProperties();
    }
}

function saveForm() {
    const formId = document.getElementById('form-id').value;
    const csrfToken = document.getElementById('csrf-token').value;

    const data = {
        schema: formSchema
    };

    const xhr = new XMLHttpRequest();
    xhr.open('POST', `/forms/${formId}/schema`, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

    xhr.onload = function() {
        if (xhr.status === 200) {
            showMessage('Form saved successfully!', 'success');
        } else {
            showMessage('Failed to save form', 'error');
        }
    };

    xhr.send(JSON.stringify(data));
}
