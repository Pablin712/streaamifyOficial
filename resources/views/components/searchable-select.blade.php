@props([
    'id' => '',
    'name' => '',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Seleccione una opción',
    'required' => false,
    'class' => '',
    'valueField' => 'id',
    'labelField' => 'name',
    'allowClear' => true,
    'dropdownParent' => null,
])

<select
    id="{{ $id }}"
    name="{{ $name }}"
    class="form-select searchable-select {{ $class }}"
    {{ $required ? 'required' : '' }}
    data-placeholder="{{ $placeholder }}"
    data-allow-clear="{{ $allowClear ? 'true' : 'false' }}"
    @if($dropdownParent) data-dropdown-parent="{{ $dropdownParent }}" @endif
    {{ $attributes }}
>
    <option value="">{{ $placeholder }}</option>
    @foreach($options as $option)
        @php
            $value = is_array($option) ? $option[$valueField] : $option->{$valueField};
            $label = is_array($option) ? $option[$labelField] : $option->{$labelField};
            $isSelected = $selected == $value;
        @endphp
        <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
