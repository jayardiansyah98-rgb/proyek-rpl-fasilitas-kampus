<?php
/**
 * AIProviderInterface
 * Kontrak abstraksi untuk provider kecerdasan buatan (Rule Engine, Gemini, OpenAI, dll.)
 */

interface AIProviderInterface {
    /**
     * Menghasilkan teks respons dari prompt konteks data
     * 
     * @param string $systemPrompt Instruksi sistem dan panduan batas data
     * @param string $userQuestion Pertanyaan pengguna
     * @param array $contextData Data faktual dari database (ground truth)
     * @return string Teks jawaban dalam format bahasa natural
     */
    public function generateAnswer(string $systemPrompt, string $userQuestion, array $contextData): string;

    /**
     * Memeriksa apakah provider siap digunakan (misal API key valid atau rule engine ready)
     * 
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Mendapatkan nama identitas provider
     * 
     * @return string
     */
    public function getName(): string;
}
