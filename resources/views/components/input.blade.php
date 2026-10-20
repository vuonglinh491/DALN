@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'rows' => 3, 'autocomplete' => null, 'placeholder' => null])
<div class="mb-3">
    <label for="f-{{ $name }}" class="form-label d-flex justify-content-between align-items-center">
        <span>{{ $label }} @if($required)<span class="text-danger" aria-hidden="true">*</span>@endif</span>
    </label>
    @if($type === 'textarea')
        <textarea id="f-{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" class="form-control @error($name) is-invalid @enderror" @required($required)
                  @if($placeholder) placeholder="{{ $placeholder }}" @endif aria-describedby="e-{{ $name }}">{{ old($name, $value) }}</textarea>
    @elseif($type === 'password')
        <div class="input-group">
            <input id="f-{{ $name }}" type="password" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror"
                   @required($required) @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                   @if($placeholder) placeholder="{{ $placeholder }}" @endif aria-describedby="e-{{ $name }}">
            <button class="btn btn-outline-secondary" type="button" tabindex="-1" onclick="var i=document.getElementById('f-{{ $name }}'), ic=this.querySelector('i'); if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}else{i.type='password';ic.className='bi bi-eye';}">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    @else
        <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror"
               value="{{ old($name, $value) }}" @required($required) @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               @if($placeholder) placeholder="{{ $placeholder }}" @endif aria-describedby="e-{{ $name }}">
    @endif
    @if($help)<div class="form-text text-muted small mt-1">{{ $help }}</div>@endif
    @error($name)<div class="invalid-feedback d-block" id="e-{{ $name }}">{{ $message }}</div>@enderror
</div>
