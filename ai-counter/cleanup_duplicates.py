import os
import io
import hashlib
import json
from pathlib import Path
from PIL import Image

FEEDBACK_DIR = Path(r"c:\xampp\htdocs\Webbuilders Projects\petshop\ai-counter\feedback")

def get_pixel_hash(img_path):
    try:
        with open(img_path, "rb") as f:
            image_bytes = f.read()
        with Image.open(io.BytesIO(image_bytes)) as img:
            pixels = img.convert("RGB").tobytes()
            return hashlib.md5(pixels).hexdigest()[:8]
    except Exception as e:
        print(f"Error hashing {img_path}: {e}")
        return None

def deduplicate():
    print(f"--- FEEDBACK CLEANUP STARTED ---")
    
    # 1. Map hash -> [files...]
    hash_map = {}
    jpg_files = list(FEEDBACK_DIR.glob("*.jpg"))
    print(f"Found {len(jpg_files)} images total.")
    
    for jpg in jpg_files:
        p_hash = get_pixel_hash(jpg)
        if p_hash:
            if p_hash not in hash_map:
                hash_map[p_hash] = []
            hash_map[p_hash].append(jpg)
            
    # 2. Process each hash group
    for p_hash, files in hash_map.items():
        if len(files) > 1:
            print(f"Found {len(files)} duplicates for hash {p_hash}")
            
            # Keep the one that matches the pixel-hash name, or just the first one
            # Actually, to be safe, find the one with the most boxes in JSON
            
            best_file = files[0]
            max_boxes = -1
            
            for f in files:
                json_path = f.with_suffix(".json")
                if json_path.exists():
                    try:
                        with json_path.open("r") as j:
                            boxes = json.load(j).get("boxes", [])
                            if len(boxes) > max_boxes:
                                max_boxes = len(boxes)
                                best_file = f
                    except: pass
            
            print(f"Keeping {best_file.name} (boxes: {max_boxes if max_boxes >= 0 else 0})")
            
            # Delete others
            for f in files:
                if f != best_file:
                    json_path = f.with_suffix(".json")
                    print(f"  Removing redundant {f.name}")
                    if f.exists(): os.remove(f)
                    if json_path.exists(): os.remove(json_path)
                    
            # Rename the "best" to its correct hash name to match the new ID system
            new_jpg = FEEDBACK_DIR / f"{p_hash}.jpg"
            new_json = FEEDBACK_DIR / f"{p_hash}.json"
            
            if best_file != new_jpg:
                old_json = best_file.with_suffix(".json")
                if new_jpg.exists(): os.remove(new_jpg) # already processed maybe?
                os.rename(best_file, new_jpg)
                if old_json.exists():
                    if new_json.exists(): os.remove(new_json)
                    os.rename(old_json, new_json)
        else:
            # Even if it's not a duplicate group, rename it to its hash for consistency
            jpg = files[0]
            if jpg.stem != p_hash:
                new_jpg = FEEDBACK_DIR / f"{p_hash}.jpg"
                new_json = FEEDBACK_DIR / f"{p_hash}.json"
                old_json = jpg.with_suffix(".json")
                print(f"Renaming {jpg.name} -> {p_hash}.jpg for consistency")
                if new_jpg.exists(): os.remove(new_jpg)
                os.rename(jpg, new_jpg)
                if old_json.exists():
                    if new_json.exists(): os.remove(new_json)
                    os.rename(old_json, new_json)

    print(f"--- CLEANUP COMPLETE ---")

if __name__ == "__main__":
    deduplicate()
