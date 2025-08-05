from playwright.sync_api import sync_playwright, TimeoutError
import json
from urllib.parse import urljoin, urlparse
import logging
import re
import random
import os
import time
import requests
from datetime import datetime
import validators

# Configure logging to console only
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[logging.StreamHandler()]
)

def random_wait(min_wait=0, max_wait=3):
    """Wait for a random time between min_wait and max_wait seconds."""
    wait_time = random.uniform(min_wait, max_wait)
    logging.info(f"Waiting for {wait_time:.2f} seconds")
    time.sleep(wait_time)

def is_valid_image(response):
    """Check if the response content is a valid image based on Content-Type."""
    content_type = response.headers.get('content-type', '')
    return content_type.startswith('image/')

def download_image(image_url, name, base_dir, user_agents):
    """Download an image from a URL and save it to a local directory."""
    try:
        if not image_url or image_url == '' or not validators.url(image_url):
            logging.warning(f"Invalid or missing image URL for {name}: {image_url}")
            return ""

        # Create directory if it doesn't exist
        images_dir = os.path.join(base_dir, 'images')
        logging.info(f"Target images directory (absolute): {os.path.abspath(images_dir)}")
        
        try:
            if not os.path.exists(images_dir):
                os.makedirs(images_dir)
                logging.info(f"Created directory: {os.path.abspath(images_dir)}")
        except PermissionError as e:
            logging.error(f"Permission denied when creating directory {images_dir}: {str(e)}")
            return ""
        except Exception as e:
            logging.error(f"Error creating directory {images_dir}: {str(e)}")
            return ""

        # Check write permission for directory
        if not os.access(images_dir, os.W_OK):
            logging.error(f"No write permission for directory {images_dir}")
            return ""

        # Generate filename: use UUID if name is too long or invalid
        safe_name = re.sub(r'[^\w\-]', '_', name.lower())[:50]  # Limit to 50 chars
        if not safe_name:
            safe_name = str(uuid.uuid4())[:8]  # Fallback to UUID
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        file_extension = os.path.splitext(urlparse(image_url).path)[1] or '.jpg'
        filename = f"{safe_name}_{timestamp}{file_extension}"
        file_path = os.path.join(images_dir, filename)

        # Download image with random User-Agent
        headers = {'User-Agent': random.choice(user_agents)}
        logging.info(f"Downloading image for {name} from {image_url} with User-Agent: {headers['User-Agent']}")
        response = requests.get(image_url, headers=headers, timeout=10)
        
        if response.status_code == 200:
            if is_valid_image(response):
                with open(file_path, 'wb') as f:
                    f.write(response.content)
                if os.path.exists(file_path) and os.path.getsize(file_path) > 0:
                    relative_path = f"images/{filename}"
                    logging.info(f"Image saved successfully: {relative_path} (Size: {os.path.getsize(file_path)} bytes)")
                    return relative_path
                else:
                    logging.error(f"Image file was not saved properly: {file_path}")
                    return ""
            else:
                logging.error(f"Downloaded content is not an image (Content-Type: {response.headers.get('content-type', 'unknown')})")
                return ""
        else:
            error_codes = {
                403: "Forbidden - Server rejected the request",
                404: "Not Found - Image URL does not exist",
                429: "Too Many Requests - Rate limit exceeded"
            }
            error_msg = error_codes.get(response.status_code, f"HTTP {response.status_code}")
            logging.error(f"Failed to download image for {name}: {error_msg} at {image_url}")
            if response.status_code == 429:
                random_wait(5, 10)  # Longer wait for rate limit
            return ""

    except Exception as e:
        logging.error(f"Error downloading image for {name}: {str(e)}")
        return ""

def extract_therapist_info(page, browser, existing_names, user_agents):
    """Extract therapist details from a page, skipping those already in existing_names."""
    therapists = []
    # Get base directory for saving images (same directory as the script)
    script_dir = os.path.dirname(os.path.abspath(__file__))
    base_dir = script_dir
    try:
        # Extract city from h1
        city = ''
        try:
            page.wait_for_selector('h1.tw-text-gray-50', timeout=10000)
            h1_element = page.query_selector('h1.tw-text-gray-50')
            if h1_element:
                h1_text = h1_element.inner_text() or ''
                city = h1_text.replace('Therapists in ', '').strip() if h1_text.startswith('Therapists in ') else h1_text
        except TimeoutError:
            logging.error(f"Timeout waiting for h1.tw-text-gray-50 on {page.url}")
        
        # Wait for therapist cards to load
        try:
            page.wait_for_selector('div[role="group"]', timeout=15000)
        except TimeoutError:
            logging.error(f"Timeout waiting for therapist cards on {page.url}")
            return []

        therapist_cards = page.query_selector_all('div[role="group"]')
        logging.info(f"Found {len(therapist_cards)} therapist cards on {page.url}")
        
        # Create a single new tab for profile pages
        profile_page = browser.new_page()
        
        for card in therapist_cards:
            # Name
            name = ''
            try:
                name_element = card.query_selector('div.name-location-cred-block p.tw-font-bold')
                name = name_element.inner_text() if name_element else ''
            except Exception as e:
                logging.error(f"Error extracting name: {str(e)}")
            
            # Skip if name already exists
            if name.lower() in existing_names:
                logging.info(f"Skipping therapist {name} as it already exists")
                continue
            
            # Avatar
            avatar_url = ''
            try:
                avatar = card.query_selector('a img')
                avatar_url = avatar.get_attribute('src') if avatar else ''
                logging.info(f"Avatar URL for {name}: {avatar_url}")
            except Exception as e:
                logging.error(f"Error extracting avatar for {name}: {str(e)}")
            avatar_local_path = download_image(avatar_url, name, base_dir, user_agents) if avatar_url != '' else ''
            
            # Title
            title = ''
            try:
                title_elements = card.query_selector_all('div.name-location-cred-block div')
                if len(title_elements) > 1:
                    title_text = title_elements[1].inner_text() or ''
                    if 'License Type' not in title_text:
                        title = title_text
            except Exception as e:
                logging.error(f"Error extracting title for {name}: {str(e)}")
            
            # Specialty
            specialty_list = ['']
            try:
                specialties = card.query_selector_all('div.specialties-block span.tw-bg-bg-success')
                specialty_list = [spec.inner_text() for spec in specialties] if specialties else ['']
            except Exception as e:
                logging.error(f"Error extracting specialties for {name}: {str(e)}")
            
            # Experience Duration
            experience = ''
            try:
                experience_elements = page.query_selector_all('div:not(.specialties-block):not(.name-location-cred-block)')
                for elem in experience_elements:
                    text = elem.inner_text() or ''
                    if 'years offering treatment' in text:
                        match = re.search(r'(\d+)\s*years offering treatment', text)
                        experience = match.group(1) if match else ''
                        break
            except Exception as e:
                logging.error(f"Error extracting experience for {name}: {str(e)}")
            
            # Extract profile link and additional areas of focus
            general_expertise = []
            profile_url = ''
            try:
                profile_link_element = card.query_selector('a:has-text("View Profile")')
                if profile_link_element:
                    profile_href = profile_link_element.get_attribute('href')
                    if profile_href:
                        profile_url = urljoin(page.url, profile_href)
                        random_wait()
                        profile_page.goto(profile_url, wait_until='networkidle', timeout=15000)
                        logging.info(f"Visiting profile page for {name}: {profile_url}")
                        focus_elements = profile_page.query_selector_all('p b:has-text("Additional areas of focus:") ~ span.hidden_counselor_secondary span[isolate]')
                        general_expertise = [elem.inner_text().strip() for elem in focus_elements if elem.inner_text().strip()] or []
                        logging.info(f"Extracted additional areas for {name}: {general_expertise}")
            except Exception as e:
                logging.error(f"Error extracting profile data for {name} at {profile_url}: {str(e)}")
            
            therapist_data = {
                'avatar': avatar_url,
                'avatar_local_path': avatar_local_path,
                'name': name,
                'title': title,
                'specialty': specialty_list,
                'experience_duration': experience,
                'city': city,
                'general_expertise': general_expertise
            }
            therapists.append(therapist_data)
            logging.info(f"Extracted data for therapist: {name} in city: {city}")
            # Save immediately to avoid data loss
            save_therapists_data([therapist_data])
            existing_names.add(name.lower())
        
        profile_page.close()
        return therapists
    except Exception as e:
        logging.error(f"Error extracting therapist info: {str(e)}")
        if 'profile_page' in locals():
            profile_page.close()
        return []

def save_therapists_data(new_data, result_file='scripts/therapists_result.json'):
    """Save therapists data to file, ensuring valid JSON."""
    try:
        existing_data = []
        if os.path.exists(result_file):
            try:
                with open(result_file, 'r', encoding='utf-8') as f:
                    existing_data = json.load(f)
            except json.JSONDecodeError as e:
                logging.error(f"Invalid JSON in {result_file}: {str(e)}. Starting with empty data.")
                existing_data = []
        existing_data.extend(new_data)
        with open(result_file, 'w', encoding='utf-8') as f:
            json.dump(existing_data, f, ensure_ascii=False, indent=4)
        logging.info(f"Saved {len(new_data)} new therapists to {result_file}")
    except Exception as e:
        logging.error(f"Error saving to {result_file}: {str(e)}")

def main():
    # Initialize Playwright
    with sync_playwright() as p:
        try:
            # Define proxy list (example proxies, replace with real ones)
            proxies = [
                # Add more proxies as needed
            ]
            proxy_index = 0

            # Define User-Agent list
            user_agents = [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:130.0) Gecko/20100101 Firefox/130.0',
                'Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.36',
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
            ]
            selected_user_agent = random.choice(user_agents)
            logging.info(f"Using User-Agent: {selected_user_agent}")

            # Load existing therapists
            existing_names = set()
            result_file = 'scripts/therapists_result.json'
            if os.path.exists(result_file):
                try:
                    with open(result_file, 'r', encoding='utf-8') as f:
                        existing_data = json.load(f)
                        existing_names = {item['name'].lower() for item in existing_data if 'name' in item}
                    logging.info(f"Loaded {len(existing_names)} existing therapist names")
                except Exception as e:
                    logging.error(f"Error reading {result_file}: {str(e)}")

            # Function to initialize browser
            def init_browser():
                nonlocal proxy_index
                if proxies and proxy_index < len(proxies):
                    proxy_config = proxies[proxy_index]
                    logging.info(f"Using proxy: {proxy_config['server']}")
                    return p.chromium.launch(headless=False, proxy=proxy_config, args=[f'--user-agent={selected_user_agent}'])
                else:
                    logging.info("No proxy used")
                    return p.chromium.launch(headless=False, args=[f'--user-agent={selected_user_agent}'])

            # Initialize browser
            browser = init_browser()
            page = browser.new_page()

            # Navigate to therapists page
            base_url = "https://www.betterhelp.com/therapists/"
            max_retries = 3
            for attempt in range(max_retries):
                try:
                    page.goto(base_url, wait_until='networkidle', timeout=15000)
                    break
                except TimeoutError:
                    logging.error(f"Timeout accessing {base_url} on attempt {attempt + 1}")
                    if attempt == max_retries - 1:
                        raise Exception(f"Failed to access {base_url} after {max_retries} attempts")
                    random_wait(2, 5)

            # Extract city and state links
            page.wait_for_selector('div.tw-text-center ul.tw-columns-2.sm\\:tw-columns-3 a', timeout=15000)
            city_links = page.query_selector_all('div.tw-text-center ul.tw-columns-2.sm\\:tw-columns-3 a')
            all_links = [urljoin(base_url, link.get_attribute('href')) for link in city_links]
            logging.info(f"Found {len(all_links)} city links")
            print(all_links)
            therapists_data = []

            # Loop through city links (limit to 5 for testing)
            for link in all_links:
                random_wait(2, 5)
                attempt = 1
                while attempt <= max_retries:
                    logging.info(f"Visiting {link} (Attempt {attempt}/{max_retries})")
                    try:
                        page.goto(link, wait_until='networkidle', timeout=15000)
                        therapists = extract_therapist_info(page, browser, existing_names, user_agents)
                        therapists_data.extend(therapists)
                        break
                    except Exception as e:
                        logging.error(f"Failed to process {link} on attempt {attempt}: {str(e)}")
                        attempt += 1
                        if attempt > max_retries:
                            if proxies and proxy_index < len(proxies) - 1:
                                proxy_index += 1
                                logging.info(f"Switching to next proxy (index {proxy_index})")
                                browser.close()
                                browser = init_browser()
                                page = browser.new_page()
                                attempt = 1
                                continue
                            else:
                                logging.error(f"Failed to process {link} after {max_retries} attempts")
                                break

        except Exception as e:
            logging.error(f"An error occurred: {str(e)}")
            if therapists_data:
                save_therapists_data(therapists_data)
        finally:
            if browser:
                browser.close()

if __name__ == "__main__":
    main()