import re

with open('e:/xampp/htdocs/filao/includes/nav.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Fix "Safari Themes" -> "Safari Experiences"
content = content.replace('<span class="mm-heading">Safari Themes</span>', '<span class="mm-heading">Safari Experiences</span>')

# 2. Reorder mobile main panel links
old_mobile_links = '''        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-tours">TOURS <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-activities">ACTIVITIES <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-destinations">DESTINATIONS <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-safaris">SAFARI EXPERIENCES <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-recommend">WE RECOMMEND <i class="fa fa-angle-right"></i></a></li>
        <li><a href="blog">BLOG</a></li>'''

new_mobile_links = '''        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-destinations">DESTINATIONS <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-tours">TOURS <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-safaris">SAFARI EXPERIENCES <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-activities">ACTIVITIES <i class="fa fa-angle-right"></i></a></li>
        <li><a href="#" class="rmm-trigger" data-target="rmm-panel-recommend">WE RECOMMEND <i class="fa fa-angle-right"></i></a></li>
        <li><a href="blog">BLOG</a></li>'''

if old_mobile_links in content:
    content = content.replace(old_mobile_links, new_mobile_links)
    print("Mobile links reordered OK")
else:
    print("WARNING: Could not find mobile links to reorder")

# 3. Desktop nav reorder: Destinations -> Tours -> Safari Experiences -> Activities -> We Recommend -> Blog
# Currently: Destinations -> Safari Experiences -> Tours -> Activities -> We Recommend -> Blog
# We need to move Safari Experiences block to after Tours block

# Extract blocks using comment markers
# Find Safari Experiences block
safari_start = content.find('          <!-- SAFARI EXPERIENCES -->')
safari_end = content.find('          <!-- TOURS -->')
if safari_start == -1 or safari_end == -1:
    print("WARNING: Could not find Safari or Tours markers")
else:
    safari_block = content[safari_start:safari_end]
    
    # Find Activities marker (end of TOURS block)
    tours_start = safari_end
    activities_start = content.find('          <!-- ACTIVITIES -->', tours_start)
    if activities_start == -1:
        print("WARNING: Could not find Activities marker")
    else:
        tours_block = content[tours_start:activities_start]
        rest = content[activities_start:]
        
        # Rebuild: Destinations block + Tours block + Safari block + rest
        dest_end = safari_start
        dest_section = content[:dest_end]
        
        new_content = dest_section + tours_block + safari_block + rest
        content = new_content
        print("Desktop nav reordered OK")

with open('e:/xampp/htdocs/filao/includes/nav.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("nav.php saved.")
