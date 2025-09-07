# Cloud Server Managements

## Prerequisites
- Node.js (v22.12 or higher)
- PHP (v8.0 or higher)
- Composer
- MySQL or any other database supported by Laravel

## Tech Stack(Track C)

- **Backend:** Laravel (PHP)
- **Frontend:** Vue.js
- **Database:** MySQL

## Setup Instructions

1. **Clone the repository:**  
    ```bash
    git clone <https://github.com/deepakdeb/cloud-server-management>  
    cd cloud-server-management
    ```

2. **Backend Setup:**  
    ```bash
    composer install  
    cp .env.example .env  
    php artisan key:generate
    ```
    
    Configure your database in the .env file. Then run migrations:  
    ```bash 
    php artisan migrate
    ```
3. **Frontend Setup:**  
    ```bash
    npm install  
    npm run dev
    ```
4. **Run the Server:**  
    ```bash
    php artisan serve  
    ```
    The application will be accessible at <http://127.0.0.1:8000>.

## API Documentation

- **GET /servers**: Fetch a list of servers with filtering, sorting, and pagination.
  - Query Parameters: search, provider, status, sort_by, sort_order, page, per_page.
- **POST /servers**: Create a new server.
- **GET /servers/{server}**: Get a single server's details.
- **PUT /servers/{server}**: Update an existing server.
- **DELETE /servers/{server}**: Delete a server.
- **POST /servers/bulk-actions**: Perform a bulk action on a list of servers.
  - Body: {"action": "delete"|"update_status", "ids": \[1, 2, 3\], "status": "active"|"inactive"|"maintenance"}

## AI Collaboration Process

- **Tools used:** I used a large language model to help with the boilerplate code and structuring the project.
- **What I asked & why:**
  - I asked for the basic Laravel project structure, including the Server model, migration, controller, and routes. This was to save time on repetitive setup tasks.
  - I also asked for the Vue.js component structure, including the main App component, a server table component, and the forms. This helped to quickly get the frontend scaffolding in place.
- **What I accepted vs. rewrote:**
  - I accepted the initial file structures and basic code for the CRUD operations.
  - I rewrote the validation logic to be more robust, ensuring the custom business rules were correctly implemented.
  - I also adjusted the frontend components for better styling and responsiveness.
- **Bugs from AI code and how I debugged them:**
  - The initial AI-generated validation rules were not comprehensive enough. For example, it did not include the unique:servers,name,NULL,id,provider,{provider} rule to ensure uniqueness per provider, which I had to add manually. This was debugged by manually testing the API endpoints with duplicate data.

## Debugging Journey

- **Chosen Debugging Challenge:** **Slow Query, Validation Edge Case, and State Desync**.
- **My approach:**
    1. **Slow Query:** For a table with thousands of records, fetching all data is inefficient. I optimized the API by explicitly selecting only the necessary columns (id, name, ip_address, provider, etc.) using the ->select() method in the ServerController. This reduces the amount of data transferred and processed for each request, drastically improving performance.
    2. **Validation Edge Case:** To prevent duplicate IP addresses from being saved, I added a unique validation rule to the ip_address field in the StoreServerRequest. This rule checks the database for existing IP addresses before a new server is created or updated. While this prevents most duplicates, a race condition could still allow two simultaneous requests to slip through. The ultimate solution for this would be to add a unique index to the ip_address column in the database migration file.
    3. **State Desync:** The frontend ServerTable.vue component was designed to prevent state desync without using an optimistic UI. Instead of assuming the API call will succeed, the component waits for a response from the server. Upon success or failure, it calls this.fetchServers(), which reloads the server list from the database. This guarantees the UI always reflects the true state of the database, gracefully handling any failed updates or other backend issues.
- **Key Takeaway:** By addressing these common issues, the application becomes more scalable, secure, and reliable. Optimizing database queries and implementing robust validation are crucial for backend performance and data integrity, while a simple data-reloading strategy on the frontend is a reliable way to manage UI state.

## Technical Decisions & Trade-offs

- **Laravel Sanctum:** I chose Sanctum for authentication due to its simplicity and suitability for a SPA. It provides a token-based system that is easy to set up.
- **Vue.js:** Vue.js was chosen for its gentle learning curve and robust component-based architecture, which makes building the frontend more manageable.
- **Tailwind:** I used tailwind for styling to quickly create a clean and responsive UI without spending too much time on custom CSS. This was a good trade-off given the 8-hour timebox.
- **Bulk Actions:** Implemented a new endpoint for bulk actions to improve efficiency for large-scale operations like deleting multiple servers at once.
- **Rate Limiting:** Added a rate limiter to the web routes to protect the application from abuse and brute-force attacks on the API endpoints.
- **Consistent Error Responses:** Ensured that the API returns consistent and meaningful error messages, which helps the frontend handle failures gracefully.

## Time Spent

- **Project Setup (Laravel & Vue):** ~1 hour
- **Backend API (CRUD & Filtering):** ~3 hours
- **Frontend (Components & API Integration):** ~3 hours
- **Debugging & Documentation:** ~1 hour
- **Total:** ~8 hours