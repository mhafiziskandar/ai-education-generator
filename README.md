# AI Education Generator

The **AI Education Generator** is a web application built using Laravel and FilamentPHP to help educators and students generate educational content, such as lesson plans, quizzes, and summaries, based on uploaded documents. It leverages the power of OpenAI's API to process educational materials (e.g., syllabi, textbooks) and generate content tailored to the input.

## Features

- **Document Upload**: Upload educational documents (e.g., syllabi, textbooks) in supported formats.
- **Content Generation**: Generate lesson plans, quizzes, summaries, and other educational content using OpenAI's GPT models.
- **FilamentPHP Admin Panel**: Manage content, view reports, and adjust settings through a user-friendly FilamentPHP interface.
- **Customizable Output**: Adjust content generation settings to suit different educational needs.
- **Easy Integration**: Built with Laravel and FilamentPHP for seamless scalability and easy administration.

## Installation

### Prerequisites

- PHP 8.1 or higher
- Laravel 9.x or higher
- Composer
- OpenAI API key

### Steps

1. Clone the repository:

    ```bash
    git clone https://github.com/mhafiziskandar/ai-education-generator.git
    ```

2. Navigate to the project directory:

    ```bash
    cd ai-education-generator
    ```

3. Install PHP dependencies:

    ```bash
    composer install
    ```

4. Set up your environment variables in the `.env` file:

    - OpenAI API key:
      ```plaintext
      OPENAI_API_KEY=your-api-key
      ```

5. Publish Filament assets and configurations:

    ```bash
    php artisan filament:install
    ```

6. Run the Laravel server:

    ```bash
    php artisan serve
    ```

7. Open your browser and navigate to `http://localhost:8000` to use the application.

## Usage

- Upload educational documents to generate relevant content such as lesson plans, quizzes, or summaries.
- Customize content generation settings via the FilamentPHP admin panel.
- View and manage generated content easily through the admin interface.

## Contributing

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-name`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature-name`).
5. Create a new Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgements

- OpenAI for providing the GPT-3/4 API.
- Laravel for the robust web application framework.
- FilamentPHP for providing a modern admin panel for easy management.