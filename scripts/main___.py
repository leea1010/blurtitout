from playwright.sync_api import sync_playwright, TimeoutError
import json
from urllib.parse import urljoin, urlparse, urlencode
import logging
import re
import random
import os
import time
import requests
from datetime import datetime
import validators
import uuid
from itertools import product
import csv
import multiprocessing
from functools import partial

# Configure logging to console and file for each process
def setup_logging(process_id):
    logging.basicConfig(
        level=logging.INFO,
        format=f'%(asctime)s - %(levelname)s - [Process {process_id}] - %(message)s',
        handlers=[
            logging.StreamHandler(),
            logging.FileHandler(f'scraper_process_{process_id}.log')
        ]
    )

def random_wait(min_wait=0, max_wait=1):
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

def apply_filters(page, link, user_agents):
    """Apply filters dynamically for a location page and return URLs with filter parameters."""
    try:
        # Click the filter button to open the modal
        filter_button_selector = 'button.btn.filter-btn[data-target="#modal-filter"]'
        page.wait_for_selector(filter_button_selector, timeout=10000)
        filter_button = page.query_selector(filter_button_selector)
        if not filter_button:
            logging.error(f"Filter button not found on {link}")
            return [(link, {})]
        filter_button.click()
        logging.info(f"Clicked filter button on {link}")
        random_wait(1, 2)

        # Wait for the filter modal to appear
        modal_selector = 'div.modal-dialog'
        page.wait_for_selector(modal_selector, timeout=10000)
        
        # Get filter options dynamically
        languages = []
        identities = []
        preferences = []

        # Extract languages
        language_select = page.query_selector('select#therapists-languages-select')
        if language_select:
            language_options = language_select.query_selector_all('option')
            languages = [(opt.get_attribute('value'), opt.inner_text().strip()) for opt in language_options if opt.get_attribute('value')]
            logging.info(f"Found languages: {languages}")
        else:
            logging.warning(f"No language filter found on {link}")

        # Extract identities (exclude Nonconforming)
        identity_select = page.query_selector('select#therapists-identities-select')
        if identity_select:
            identity_options = identity_select.query_selector_all('option')
            identities = [(opt.get_attribute('value'), opt.inner_text().strip()) for opt in identity_options if opt.get_attribute('value') and opt.get_attribute('value') != 'Nonconforming']
            logging.info(f"Found identities (excluding Nonconforming): {identities}")
        else:
            logging.warning(f"No identity filter found on {link}")

        # Extract preferences
        preference_select = page.query_selector('select#therapists-preferences-select')
        if preference_select:
            preference_options = preference_select.query_selector_all('option')
            preferences = [(opt.get_attribute('value'), opt.inner_text().strip()) for opt in preference_options if opt.get_attribute('value')]
            logging.info(f"Found preferences: {preferences}")
        else:
            logging.warning(f"No preference filter found on {link}")

        # Close the modal
        close_button = page.query_selector('button.close[data-dismiss="modal"]')
        if close_button:
            close_button.click()
            logging.info("Closed filter modal")
            random_wait(1, 2)

        # Prepare filter options and their corresponding types
        filter_options = []
        filter_types = []
        if languages:
            filter_options.append(languages)
            filter_types.append('languages')
        if identities:
            filter_options.append(identities)
            filter_types.append('identities')
        if preferences:
            filter_options.append(preferences)
            filter_types.append('preferences')

        # Generate filter URLs based on available filters
        filter_urls = []
        if not filter_options:
            logging.info(f"No filters available for {link}")
            return [(link, {})]

        # Create all combinations of available filters
        for combination in product(*filter_options):
            params = {}
            filters = {}
            for i, (value, label) in enumerate(combination):
                filter_type = filter_types[i]
                params[filter_type] = value
                filters[filter_type] = label
            filter_url = f"{link}?{urlencode(params)}"
            filter_urls.append((filter_url, filters))
            logging.info(f"Generated filter URL: {filter_url} with filters: {filters}")

        # Log the HTML of the modal for debugging if no languages found
        if not languages:
            modal_html = page.query_selector(modal_selector).inner_html() if page.query_selector(modal_selector) else ""
            logging.debug(f"Filter modal HTML (no languages found): {modal_html}")

        return filter_urls

    except TimeoutError:
        logging.error(f"Timeout while applying filters on {link}")
        return [(link, {})]
    except Exception as e:
        logging.error(f"Error applying filters on {link}: {str(e)}")
        # Log the HTML of the modal for debugging
        modal_html = page.query_selector(modal_selector).inner_html() if page.query_selector(modal_selector) else ""
        logging.debug(f"Filter modal HTML (error case): {modal_html}")
        return [(link, {})]

def extract_therapist_info(page, browser, user_agents, filters=None):
    """Extract therapist details from a page."""
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
            
            # Avatar
            avatar_url = ''
            try:
                avatar = card.query_selector('a img')
                avatar_url = avatar.get_attribute('src') if avatar else ''
                logging.info(f"Avatar URL for {name}: {avatar_url}")
            except Exception as e:
                logging.error(f"Error extracting avatar for {name}: {str(e)}")
            avatar_local_path = download_image(avatar_url, name, base_dir, user_agents) if avatar_url != '' else ''
            
            # License (from therapist card)
            license = ''
            try:
                license_element = card.query_selector('div[notranslate]:has-text("License: ")')
                if license_element:
                    license_text = license_element.inner_text().strip() or ''
                    logging.info(f"Raw license text for {name}: {license_text}")
                    if license_text.startswith('License: '):
                        license = license_text.replace('License: ', '').strip()
                    else:
                        license = ''
                    logging.info(f"Extracted license for {name}: {license}")
                else:
                    logging.warning(f"No license element found for {name} in card")
            except Exception as e:
                logging.error(f"Error extracting license for {name}: {str(e)}")
                # Log the inner HTML of the card for debugging
                card_html = card.inner_html() if card else ''
                logging.debug(f"Card HTML for {name}: {card_html}")
            
            # Specialty
            specialty_list = ['']
            try:
                specialties = card.query_selector_all('div.specialties-block span.tw-bg-bg-success')
                specialty_list = [spec.inner_text() for spec in specialties] if specialties else ['']
            except Exception as e:
                logging.error(f"Error extracting specialties for {name}: {str(e)}")
            
            # Extract profile link, additional areas of focus, state, state_code, clinical approaches, services offered, about, experience, and languages
            general_expertise = []
            profile_url = ''
            state = []
            state_code = []
            clinical_approaches = ['']
            online_offered = ['']
            about = ''
            experience_duration = ''
            experience = ''
            languages = ['']
            link_to_website = ''
            try:
                profile_link_element = card.query_selector('a:has-text("View Profile")')
                if profile_link_element:
                    profile_href = profile_link_element.get_attribute('href')
                    if profile_href:
                        profile_url = urljoin(page.url, profile_href)
                        random_wait()
                        profile_page.goto(profile_url, wait_until='networkidle', timeout=15000)
                        logging.info(f"Visiting profile page for {name}: {profile_url}")
                        
                        # Extract "Work with me!" link
                        try:
                            profile_page.wait_for_selector('div.counselor-profile-header__cta a', timeout=10000)
                            work_with_me_a = profile_page.query_selector('div.counselor-profile-header__cta a')
                            if work_with_me_a:
                                work_href = work_with_me_a.get_attribute('href')
                                if work_href:
                                    link_to_website = urljoin(profile_page.url, work_href)
                                    logging.info(f"Extracted link_to_website for {name}: {link_to_website}")
                                else:
                                    logging.warning(f"No href found in Work with me button for {name}")
                            else:
                                logging.warning(f"No Work with me button found for {name} on {profile_url}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for Work with me button on {profile_url}")
                        except Exception as e:
                            logging.error(f"Error extracting Work with me link for {name} on {profile_url}: {str(e)}")
                        
                        # Extract title from profile page
                        try:
                            profile_page.wait_for_selector('h1.counselor-profile-header__name', timeout=10000)
                            title_element = profile_page.query_selector('h1.counselor-profile-header__name')
                            if title_element:
                                title_text = title_element.inner_text() or ''
                                logging.info(f"Raw title text for {name}: {title_text}")
                                # Split by comma and take all parts after the first one, join back with commas
                                title_parts = title_text.split(',')
                                title = ','.join(title_parts[1:]).strip() if len(title_parts) > 1 else ''
                                logging.info(f"Extracted title for {name}: {title}")
                            else:
                                logging.warning(f"No title element found for {name} on {profile_url}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for h1.counselor-profile-header__name on {profile_url}")
                        except Exception as e:
                            logging.error(f"Error extracting title for {name} on {profile_url}: {str(e)}")
                        
                        # Extract additional areas of focus
                        focus_elements = profile_page.query_selector_all('p b:has-text("Additional areas of focus:") ~ span.hidden_counselor_secondary span[isolate]')
                        general_expertise = [elem.inner_text().strip() for elem in focus_elements if elem.inner_text().strip()] or []
                        logging.info(f"Extracted additional areas for {name}: {general_expertise}")
                        
                        # Extract state and state_code from licensing section
                        try:
                            profile_page.wait_for_selector('div#licensing h2.content__title:has-text("License information")', timeout=10000)
                            license_elements = profile_page.query_selector_all('div#licensing p')
                            state_list = []
                            state_code_list = []
                            if license_elements:
                                license_texts = [elem.inner_text().strip() for elem in license_elements if elem.inner_text().strip()]
                                logging.info(f"Raw license texts for {name}: {license_texts}")
                                for license_text in license_texts:
                                    license_parts = license_text.split()
                                    if len(license_parts) >= 2:
                                        state_list.append(license_parts[0])
                                        state_code_list.append(license_parts[-1])
                                state = state_list
                                state_code = state_code_list
                                logging.info(f"Extracted state for {name}: {state}, state_code: {state_code}")
                            else:
                                logging.warning(f"No license information found for {name} on {profile_url}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for license information on {profile_url}")
                        except Exception as e:
                            logging.error(f"Error extracting license information for {name} on {profile_url}: {str(e)}")
                        
                        # Extract clinical approaches
                        try:
                            profile_page.wait_for_selector('div#professional-experience p:has-text("Clinical approaches:")', timeout=10000)
                            clinical_approach_elements = profile_page.query_selector_all('div#professional-experience p:has-text("Clinical approaches:") span[isolate]')
                            clinical_approaches = [elem.inner_text().strip() for elem in clinical_approach_elements if elem.inner_text().strip()] or ['']
                            logging.info(f"Extracted clinical approaches for {name}: {clinical_approaches}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for clinical approaches on {profile_url}")
                        except Exception as e:
                            logging.error(f"Error extracting clinical approaches for {name} on {profile_url}: {str(e)}")
                        
                        # Extract services offered
                        try:
                            profile_page.wait_for_selector('div#services-offered div.services-offered-container', timeout=10000)
                            service_elements = profile_page.query_selector_all('div#services-offered div.services-offered-container span')
                            online_offered = [elem.inner_text().strip() for elem in service_elements if elem.inner_text().strip()] or ['']
                            logging.info(f"Extracted services offered for {name}: {online_offered}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for services offered on {profile_url}")
                        except Exception as e:
                            logging.error(f"Error extracting services offered for {name} on {profile_url}: {str(e)}")
                        
                        # Extract about information
                        try:
                            profile_page.wait_for_selector('div#about p', timeout=10000)
                            about_element = profile_page.query_selector('div#about p')
                            about = about_element.inner_text().strip() if about_element and about_element.inner_text().strip() else ''
                            logging.info(f"Extracted about for {name}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for about information on {profile_url}")
                        except Exception as e:
                            logging.error(f"Error extracting about for {name} on {profile_url}: {str(e)}")
                        
                        # Extract experience duration and full experience text
                        try:
                            profile_page.wait_for_selector('div#professional-experience span.bg-yellow-100.tag', timeout=10000)
                            experience_element = profile_page.query_selector('div#professional-experience span.bg-yellow-100.tag')
                            if experience_element:
                                experience_text = experience_element.inner_text().strip() or ''
                                logging.info(f"Raw experience text for {name}: {experience_text}")
                                experience = experience_text
                                # Extract only the number of years for experience_duration
                                match = re.search(r'(\d+)', experience_text)
                                experience_duration = match.group(1) if match else ''
                                logging.info(f"Extracted experience_duration for {name}: {experience_duration}")
                            else:
                                logging.warning(f"No experience element found for {name} on {profile_url}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for experience information on {profile_url}")
                        except Exception as e:
                            logging.error(f"Error extracting experience for {name} on {profile_url}: {str(e)}")
                        
                        # Extract languages from profile
                        try:
                            profile_page.wait_for_selector('div#languages div.tag__container', timeout=10000)
                            language_elements = profile_page.query_selector_all('div#languages div.tag__container span.bg-blue-100.tag')
                            languages = [elem.inner_text().strip() for elem in language_elements if elem.inner_text().strip()] or ['']
                            logging.info(f"Extracted languages for {name}: {languages}")
                        except TimeoutError:
                            logging.error(f"Timeout waiting for languages on {profile_url}")
                            languages = ['']
                        except Exception as e:
                            logging.error(f"Error extracting languages for {name} on {profile_url}: {str(e)}")
                            languages = ['']
                            
            except Exception as e:
                logging.error(f"Error extracting profile data for {name} at {profile_url}: {str(e)}")
            
            # Use filter values for languages if profile languages are empty or ['']
            if not languages or languages == ['']:
                languages = [filters.get('languages', '')] if filters else ['']
                logging.info(f"No valid languages from profile for {name}, using filter language: {languages}")
            
            # Default to ['English'] if languages is still empty or ['']
            if not languages or languages == ['']:
                languages = ['English']
                logging.info(f"No valid languages from profile or filter for {name}, defaulting to: {languages}")
            
            # Use filter values for gender and other_traits
            gender = filters.get('identities', '') if filters else ''
            other_traits = filters.get('preferences', '') if filters else ''
            
            therapist_data = {
                'avatar': avatar_url,
                'avatar_local_path': avatar_local_path,
                'name': name,
                'title': title,
                'license': license,
                'specialty': specialty_list,
                'experience_duration': experience_duration,
                'experience': experience,
                'city': city,
                'general_expertise': general_expertise,
                'link_to_website': link_to_website,
                'services_offered': 'Online',
                'country': 'United States',
                'state': state,
                'state_code': state_code,
                'type_of_therapy': 'Individual Therapy',
                'payment_method': ['Credit Card', 'PayPal'],
                'clinical_approaches': clinical_approaches,
                'online_offered': online_offered,
                'about': about,
                'languages': languages,
                'gender': gender,
                'other_traits': other_traits
            }
            therapists.append(therapist_data)
            logging.info(f"Extracted data for therapist: {name} in city: {city} with filters: languages={languages}, gender={gender}, other_traits={other_traits}")
            # Save immediately to avoid data loss
            save_therapists_data([therapist_data], result_file=f'therapists_result_process_{multiprocessing.current_process().name}.json')
        
        profile_page.close()
        return therapists
    except Exception as e:
        logging.error(f"Error extracting therapist info: {str(e)}")
        if 'profile_page' in locals():
            profile_page.close()
        return []

def save_therapists_data(new_data, result_file='therapists_result.json'):
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

def worker(city_state_pairs, proxies, user_agents, process_id):
    """Worker function to process a subset of city-state pairs."""
    setup_logging(process_id)
    with sync_playwright() as p:
        try:
            proxy_index = 0
            selected_user_agent = random.choice(user_agents)
            logging.info(f"Using User-Agent: {selected_user_agent}")

            def init_browser(proxy_config):
                logging.info(f"Initializing browser with proxy: {proxy_config['server']}")
                return p.chromium.launch(
                    headless=True,
                    proxy=proxy_config,
                    args=[f'--user-agent={selected_user_agent}']
                )

            browser = init_browser(proxies[proxy_index])
            page = browser.new_page()
            base_url = "https://www.betterhelp.com/therapists/"
            max_retries = 3
            therapists_data = []

            for pair in city_state_pairs:
                city = pair['city'].lower().replace(' ', '-')  # Format city name for URL
                state = pair['state'].lower()
                link = f"{base_url}{state}/{city}/"
                
                while proxy_index < len(proxies):
                    attempt = 1
                    success = False
                    while attempt <= max_retries:
                        logging.info(f"Visiting {link} (Attempt {attempt}/{max_retries}, Proxy {proxy_index + 1}/{len(proxies)}: {proxies[proxy_index]['server']})")
                        try:
                            page.goto(link, wait_until='networkidle', timeout=15000)
                            # Check breadcrumb for exactly 2 <i> elements
                            breadcrumb_selector = 'div[role="breadcrumb"]'
                            try:
                                page.wait_for_selector(breadcrumb_selector, timeout=10000)
                                breadcrumb = page.query_selector(breadcrumb_selector)
                                if breadcrumb:
                                    li_elements = breadcrumb.query_selector_all('i')
                                    li_count = len(li_elements)
                                    logging.info(f"Found {li_count} <i> elements in breadcrumb for {link}")
                                    if li_count != 2:
                                        logging.warning(f"Breadcrumb does not contain exactly 2 <i> elements for {link}. Skipping to next city-state pair.")
                                        success = True
                                        break
                                else:
                                    logging.warning(f"Breadcrumb div not found for {link}. Skipping to next city-state pair.")
                                    success = True
                                    break
                            except TimeoutError:
                                logging.error(f"Timeout waiting for breadcrumb div on {link}. Skipping to next city-state pair.")
                                success = True
                                break
                            except Exception as e:
                                logging.error(f"Error checking breadcrumb for {link}: {str(e)}. Skipping to next city-state pair.")
                                success = True
                                break

                            # Apply filters and get URLs with filter parameters
                            filter_urls = apply_filters(page, link, user_agents)
                            for item in filter_urls:
                                if isinstance(item, tuple) and len(item) == 2:
                                    filter_url, filters = item
                                else:
                                    filter_url = item
                                    filters = {}
                                logging.info(f"Processing filtered URL: {filter_url} with filters: {filters}")
                                attempt_filter = 1
                                while attempt_filter <= max_retries:
                                    try:
                                        page.goto(filter_url, wait_until='networkidle', timeout=15000)
                                        therapists = extract_therapist_info(page, browser, user_agents, filters)
                                        therapists_data.extend(therapists)
                                        success = True
                                        break
                                    except Exception as e:
                                        logging.error(f"Failed to process {filter_url} on attempt {attempt_filter}: {str(e)}")
                                        attempt_filter += 1
                                        if attempt_filter > max_retries:
                                            logging.error(f"Failed to process {filter_url} after {max_retries} attempts")
                                            break
                                        random_wait(2, 5)
                            break
                        except Exception as e:
                            logging.error(f"Failed to process {link} on attempt {attempt}: {str(e)}")
                            attempt += 1
                            if attempt > max_retries:
                                logging.error(f"Failed to process {link} after {max_retries} attempts with proxy {proxies[proxy_index]['server']}")
                                break
                            random_wait(2, 5)
                    
                    if success:
                        break
                    else:
                        proxy_index += 1
                        if proxy_index < len(proxies):
                            logging.info(f"Switching to next proxy (index {proxy_index}): {proxies[proxy_index]['server']}")
                            browser.close()
                            browser = init_browser(proxies[proxy_index])
                            page = browser.new_page()
                        else:
                            logging.error(f"All proxies failed for {link}. Moving to next city-state pair.")
                            break
                if proxy_index >= len(proxies):
                    logging.warning(f"All proxies exhausted for {link}. Continuing with next city-state pair.")

            if therapists_data:
                save_therapists_data(therapists_data, result_file=f'therapists_result_process_{process_id}.json')

        except Exception as e:
            logging.error(f"An error occurred in process {process_id}: {str(e)}")
            if therapists_data:
                save_therapists_data(therapists_data, result_file=f'therapists_result_process_{process_id}.json')
        finally:
            if 'browser' in locals():
                browser.close()

def merge_results():
    """Merge all process result JSON files into a single file."""
    final_result_file = 'therapists_result.json'
    all_data = []
    for process_id in range(2):  # Assuming 2 processes
        result_file = f'therapists_result_process_{process_id}.json'
        if os.path.exists(result_file):
            try:
                with open(result_file, 'r', encoding='utf-8') as f:
                    data = json.load(f)
                    all_data.extend(data)
                logging.info(f"Merged data from {result_file}")
            except Exception as e:
                logging.error(f"Error reading {result_file}: {str(e)}")
    
    if all_data:
        try:
            with open(final_result_file, 'w', encoding='utf-8') as f:
                json.dump(all_data, f, ensure_ascii=False, indent=4)
            logging.info(f"Merged {len(all_data)} records into {final_result_file}")
        except Exception as e:
            logging.error(f"Error saving merged data to {final_result_file}: {str(e)}")

def main():
    # Setup main process logging
    setup_logging('main')
    
    # Define proxy list
    proxies = [
    
    ]
    
    # Split proxies for two processes
    proxy_split = len(proxies) // 2
    proxies_1 = proxies[:proxy_split + (len(proxies) % 2)]  # Ensure first process gets extra proxy if odd number
    proxies_2 = proxies[proxy_split + (len(proxies) % 2):]
    logging.info(f"Proxies for process 0: {[p['server'] for p in proxies_1]}")
    logging.info(f"Proxies for process 1: {[p['server'] for p in proxies_2]}")

    # Define User-Agent list
    user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:130.0) Gecko/20100101 Firefox/130.0',
        'Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
    ]

    # Load city-state pairs from CSV file
    city_state_pairs = []
    csv_file_path = 'city-state.csv'
    try:
        with open(csv_file_path, 'r', encoding='utf-8') as csv_file:
            csv_reader = csv.reader(csv_file)
            for row in csv_reader:
                if len(row) >= 2:
                    city, state = row[0].strip(), row[1].strip()
                    if city and state:
                        city_state_pairs.append({'city': city, 'state': state})
                    else:
                        logging.warning(f"Skipping invalid row in CSV: {row}")
                else:
                    logging.warning(f"Skipping malformed row in CSV: {row}")
        logging.info(f"Loaded {len(city_state_pairs)} city-state pairs from {csv_file_path}")
    except FileNotFoundError:
        logging.error(f"CSV file not found: {csv_file_path}. Exiting.")
        return
    except Exception as e:
        logging.error(f"Error reading CSV file {csv_file_path}: {str(e)}. Exiting.")
        return

    # Split city-state pairs for two processes
    split_index = len(city_state_pairs) // 2
    city_state_pairs_1 = city_state_pairs[:split_index]
    city_state_pairs_2 = city_state_pairs[split_index:]
    logging.info(f"Process 0 will handle {len(city_state_pairs_1)} city-state pairs")
    logging.info(f"Process 1 will handle {len(city_state_pairs_2)} city-state pairs")

    # Run two processes
    with multiprocessing.Pool(processes=2) as pool:
        pool.starmap(worker, [
            (city_state_pairs_1, proxies_1, user_agents, 0),
            (city_state_pairs_2, proxies_2, user_agents, 1)
        ])

    # Merge results from both processes
    merge_results()

if __name__ == "__main__":
    main()