import re

file_path = "/Users/macbookpro/RND_DDT/RND_FINAL/resources/views/livewire/operator/single-operator-form.blade.php"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. First, replace any emoji span (like ↔️ or 🆔 or empty) inside the class "absolute left-6 top-1/2 -translate-y-1/2 text-lg opacity-40 group-focus-within:opacity-100 transition-opacity"
# and change the sibling input's padding from pl-16 to pl-6.

# Let's match:
# <div class="relative">
#     <span class="absolute left-6 top-1/2 -translate-y-1/2 text-lg opacity-40 group-focus-within:opacity-100 transition-opacity">...</span>
#     <input ... class="... pl-16 ...">
# </div>

pattern = re.compile(
    r'(<div class="relative">\s*)<span class="absolute left-6 top-1/2 -translate-y-1/2 text-lg opacity-40 group-focus-within:opacity-100 transition-opacity">.*?</span>(\s*<input[^>]+class="[^"]*)pl-16([^"]*"[^>]*>)',
    re.DOTALL
)

def replacer(match):
    # We replace pl-16 with pl-6 and remove the span tag
    prefix = match.group(1)
    input_prefix = match.group(2)
    input_suffix = match.group(3)
    return f"{prefix}{input_prefix}pl-6{input_suffix}"

new_content = pattern.sub(replacer, content)

# 2. Also handle the dynamic inputs inside the loop:
# In single-operator-form.blade.php around line 1803:
# <span class="absolute left-6 top-1/2 -translate-y-1/2 text-lg opacity-40 group-focus-within:opacity-100 transition-opacity">{{ $icon }}</span>
# <input ... class="... pl-16 ...">

# Let's make it conditional: if $icon is set, show it and use pl-16. If not, don't show it and use pl-6.
dynamic_pattern = re.compile(
    r'<span class="absolute left-6 top-1/2 -translate-y-1/2 text-lg opacity-40 group-focus-within:opacity-100 transition-opacity">\{\{\s*\$icon\s*\}\}</span>(\s*)<input([^>]+)class="([^"]*)pl-16([^"]*)"',
    re.DOTALL
)

def dynamic_replacer(match):
    whitespace = match.group(1)
    input_body = match.group(2)
    class_prefix = match.group(3)
    class_suffix = match.group(4)
    
    new_span = '@if($icon)\n                                                                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-lg opacity-40 group-focus-within:opacity-100 transition-opacity">{{ $icon }}</span>\n                                                                            @endif'
    new_input = f'<input{input_body}class="{class_prefix}{{{{ $icon ? \'pl-16\' : \'pl-6\' }}}}{class_suffix}"'
    return f"{new_span}{whitespace}{new_input}"

new_content = dynamic_pattern.sub(dynamic_replacer, new_content)

# 3. Clean up the $fieldIcons in app/Livewire/Operator/SingleOperatorForm.php to remove emojis
# Let's check single-operator-form.blade.php line 1764 or so where $fieldIcons is defined.
# Wait, is $fieldIcons defined in the blade view itself? Yes, line 1764 has $fieldIcons.
# Let's remove any emojis from $fieldIcons definition in the blade view:
# 'lebar' => '↔️' -> 'lebar' => ''
# 'kode_warna' => '🆔' -> 'kode_warna' => ''
new_content = new_content.replace("'lebar' => '↔️'", "'lebar' => ''")
new_content = new_content.replace("'kode_warna' => '🆔'", "'kode_warna' => ''")

with open(file_path, "w", encoding="utf-8") as f:
    f.write(new_content)

print("Replacement complete.")
