document.addEventListener('DOMContentLoaded', () => {

    // --- Summernote Initialization ---
    // Full editor for main description
    $('.editor-full').summernote({
        height: 500,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Simple editor for highlights/inclusions/etc.
    $('.editor-simple').summernote({
        height: 250,
        toolbar: [
            ['font', ['bold', 'italic', 'clear']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['view', ['codeview']]
        ]
    });

    // Initialize existing step descriptions
    $('.step-desc-editor').summernote({
        height: 200,
        toolbar: [
            ['font', ['bold', 'italic', 'clear']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']]
        ]
    });

    // --- Slug Generator ---
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const toggleSlugBtn = document.getElementById('toggleSlug');
    let slugLocked = true;

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', () => {
            if (slugLocked && !slugInput.hasAttribute('data-persisted')) {
                slugInput.value = titleInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
            }
        });

        toggleSlugBtn.addEventListener('click', () => {
            slugLocked = !slugLocked;
            slugInput.readOnly = slugLocked;
            toggleSlugBtn.innerHTML = slugLocked ? '<i class="bi bi-lock-fill"></i>' : '<i class="bi bi-unlock-fill"></i>';
            if(!slugLocked) slugInput.focus();
        });
    }

    // --- Featured Image Upload Preview ---
    const zone = document.getElementById('featuredImgZone');
    const input = document.getElementById('featured_image');
    const previewContainer = document.getElementById('featuredPreviewContainer');
    const previewImg = document.getElementById('featuredPreview');
    const removeBtn = document.getElementById('removeFeaturedImg');

    if (zone && input) {
        zone.addEventListener('click', () => input.click());
        
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
        
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                triggerPreview();
            }
        });

        input.addEventListener('change', triggerPreview);

        removeBtn.addEventListener('click', () => {
            input.value = '';
            previewContainer.classList.add('d-none');
            zone.classList.remove('d-none');
            
            // if editing and removing existing
            const hidden = document.getElementById('existing_featured_image');
            if(hidden) hidden.value = '';
        });

        function triggerPreview() {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                    zone.classList.add('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    }

    // --- Itinerary Builder ---
    const container = document.getElementById('itineraryContainer');
    const addBtn = document.getElementById('addStepBtn');
    let stepCount = document.querySelectorAll('.step-card').length || 0;

    if (container && addBtn) {
        // Initialize Sortable
        if (typeof Sortable !== 'undefined') {
            new Sortable(container, {
                animation: 150,
                handle: '.step-handle',
                ghostClass: 'bg-light',
                onEnd: updateStepNumbers
            });
        }

        addBtn.addEventListener('click', () => {
            const destOptions = typeof destinations !== 'undefined' ? destinations.map(d => `<option value="${d.id}">${d.name}</option>`).join('') : '';
            const accomOptions = typeof accommodations !== 'undefined' ? accommodations.map(a => `<option value="${a.id}">${a.name}</option>`).join('') : '';

            const stepHtml = `
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-handle me-2" style="cursor: grab; color: var(--fa-muted);"><i class="bi bi-grip-vertical fs-5"></i></div>
                        <div class="step-number-badge">${stepCount + 1}</div>
                        <div class="step-title-label">Day ${stepCount + 1}</div>
                        <button type="button" class="step-remove-btn"><i class="bi bi-trash"></i> Remove Day</button>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Step Title / Heading</label>
                            <input type="text" class="form-control" name="steps[${stepCount}][title]" placeholder="e.g. Arrival in Nairobi & Transfer">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nights Stay</label>
                            <input type="number" class="form-control" name="steps[${stepCount}][nights]" value="1" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destination</label>
                            <select class="form-select" name="steps[${stepCount}][destination_id]" required>
                                <option value="">Select Destination...</option>
                                ${destOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Accommodation (Optional)</label>
                            <select class="form-select" name="steps[${stepCount}][accommodation_id]">
                                <option value="">None / Not Applicable</option>
                                ${accomOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transit Mode</label>
                            <input type="text" class="form-control" name="steps[${stepCount}][transit_mode]" placeholder="e.g. 4x4 Safari Vehicle, Light Aircraft" value="4x4 Safari Vehicle">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transit Duration</label>
                            <input type="text" class="form-control" name="steps[${stepCount}][transit_duration]" placeholder="e.g. 2 hours, 45 minutes flight">
                        </div>
                        <div class="col-12">
                            <label class="editor-label">Daily Description <span class="text-danger">*</span></label>
                            <textarea class="form-control step-desc-editor" name="steps[${stepCount}][description]" rows="4"></textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label"><i class="bi bi-image"></i> Step Image (Optional)</label>
                            <input class="form-control form-control-sm" type="file" name="steps[${stepCount}][image]" accept="image/*">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', stepHtml);
            
            // Init minimal summernote for this specific step
            const newTextarea = container.lastElementChild.querySelector('.step-desc-editor');
            newTextarea.id = 'step_desc_' + stepCount;
            $(newTextarea).summernote({
                height: 200,
                toolbar: [
                    ['font', ['bold', 'italic', 'clear']],
                    ['para', ['ul', 'ol']],
                    ['insert', ['link']]
                ]
            });

            stepCount++;
            updateStepNumbers();
        });

        container.addEventListener('click', (e) => {
            if (e.target.closest('.step-remove-btn')) {
                const card = e.target.closest('.step-card');
                // destroy summernote instance if exists
                const textarea = card.querySelector('.step-desc-editor');
                if (textarea) {
                    $(textarea).summernote('destroy');
                }
                card.remove();
                updateStepNumbers();
            }
        });

        function updateStepNumbers() {
            const cards = container.querySelectorAll('.step-card');
            cards.forEach((card, index) => {
                const num = index + 1;
                card.querySelector('.step-number-badge').textContent = num;
                card.querySelector('.step-title-label').textContent = 'Day ' + num;
            });
        }

        // Add first step by default if empty
        if (stepCount === 0) {
            addBtn.click();
        }
    }

    // --- SEO Counters ---
    const seoTitle = document.getElementById('seo_title');
    const seoTitleCounter = document.getElementById('seo_title_counter');
    const metaDesc = document.getElementById('meta_description');
    const metaDescCounter = document.getElementById('meta_description_counter');

    if (seoTitle && seoTitleCounter) {
        seoTitle.addEventListener('input', () => {
            const len = seoTitle.value.length;
            seoTitleCounter.textContent = `${len} / 60 chars`;
            seoTitleCounter.className = len > 60 ? 'char-counter over-limit' : 'char-counter';
        });
        // Init
        seoTitle.dispatchEvent(new Event('input'));
    }

    if (metaDesc && metaDescCounter) {
        metaDesc.addEventListener('input', () => {
            const len = metaDesc.value.length;
            metaDescCounter.textContent = `${len} / 160 chars`;
            metaDescCounter.className = len > 160 ? 'char-counter over-limit' : 'char-counter';
        });
        // Init
        metaDesc.dispatchEvent(new Event('input'));
    }

    // --- Form Validation across Tabs ---
    const saveBtn = document.getElementById('saveBtn');
    const tourForm = document.getElementById('tourForm');

    if (saveBtn && tourForm) {
        saveBtn.addEventListener('click', function(e) {
            // If the form itself has standard invalid elements
            if (!tourForm.checkValidity()) {
                e.preventDefault();
                
                // Find the first invalid element
                const firstInvalid = tourForm.querySelector(':invalid');
                if (firstInvalid) {
                    // Check if it is inside a tab pane
                    const tabPane = firstInvalid.closest('.tab-pane');
                    if (tabPane) {
                        const tabId = tabPane.id;
                        // Find the nav button for this tab pane
                        const tabLink = document.querySelector(`[data-bs-target="#${tabId}"]`) || document.querySelector(`[href="#${tabId}"]`);
                        if (tabLink) {
                            // Switch to that tab
                            const tab = new bootstrap.Tab(tabLink);
                            tab.show();
                        }
                    }
                    
                    // Show a nice alert then focus
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Information',
                            text: 'Please fill out all required fields. We have highlighted the missing field for you.',
                            confirmButtonColor: '#C49018'
                        }).then(() => {
                            firstInvalid.focus();
                        });
                    } else {
                        alert('Please fill out all required fields. We have highlighted the missing field for you.');
                        firstInvalid.focus();
                    }
                }
            }
        });
    }

});
