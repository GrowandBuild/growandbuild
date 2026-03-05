<?php
// Gerador de ícones PWA usando imagem SVG personalizada

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];
$imagePath = __DIR__ . '/../foto do app.svg';
$outputDir = __DIR__;

echo "🎨 Gerador de Ícones PWA com Imagem Personalizada\n";
echo "================================================\n\n";

// Verificar se a imagem SVG existe
if (!file_exists($imagePath)) {
    echo "❌ Erro: Imagem SVG não encontrada em: $imagePath\n";
    echo "   Verifique se o arquivo 'foto do app.svg' está na raiz do projeto.\n";
    exit(1);
}

echo "✅ Imagem SVG encontrada: $imagePath\n";
echo "📏 Tamanhos a gerar: " . implode(', ', $sizes) . "\n";
echo "📁 Diretório de saída: $outputDir\n\n";

// Função para criar ícone com SVG
function createIconFromSVG($size, $svgPath) {
    $canvas = imagecreatetruecolor($size, $size);
    
    // Fundo branco
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);
    
    // Tentar carregar SVG
    $svgContent = file_get_contents($svgPath);
    if ($svgContent === false) {
        echo "⚠️  Não foi possível ler o arquivo SVG\n";
        return createDefaultIcon($canvas, $size);
    }
    
    // Criar imagem temporária do SVG
    $tempFile = tempnam(sys_get_temp_dir(), 'svg_temp_');
    $pngFile = $tempFile . '.png';
    
    // Usar ImageMagick ou conversão SVG para PNG
    if (extension_loaded('imagick')) {
        try {
            $imagick = new Imagick();
            $imagick->readImageBlob($svgContent);
            $imagick->setImageFormat('png');
            $imagick->scaleImage($size * 0.8, $size * 0.8, true);
            
            // Centralizar a imagem
            $svgWidth = $imagick->getImageWidth();
            $svgHeight = $imagick->getImageHeight();
            $x = ($size - $svgWidth) / 2;
            $y = ($size - $svgHeight) / 2;
            
            imagecopy($canvas, $imagick, $x, $y, 0, 0, $svgWidth, $svgHeight);
            $imagick->clear();
            $imagick->destroy();
            
        } catch (Exception $e) {
            echo "⚠️  Erro com ImageMagick: " . $e->getMessage() . "\n";
            return createDefaultIcon($canvas, $size);
        }
    } else {
        // Fallback: tentar usar conversão externa ou criar ícone padrão
        echo "⚠️  ImageMagick não disponível, usando ícone padrão\n";
        return createDefaultIcon($canvas, $size);
    }
    
    // Limpar arquivos temporários
    if (file_exists($tempFile)) unlink($tempFile);
    if (file_exists($pngFile)) unlink($pngFile);
    
    return $canvas;
}

// Função para criar ícone padrão
function createDefaultIcon($canvas, $size) {
    $centerX = $size / 2;
    $centerY = $size / 2;
    $radius = $size * 0.3;
    
    // Fundo branco já foi preenchido
    
    // Círculo verde
    $green = imagecolorallocate($canvas, 16, 185, 129);
    imagefilledellipse($canvas, $centerX, $centerY, $radius * 2, $radius * 2, $green);
    
    // Letra "F" branca
    $white = imagecolorallocate($canvas, 255, 255, 255);
    $fontSize = $size * 0.4;
    
    // Usar built-in font para evitar dependências
    $text = "F";
    $fontWidth = imagefontwidth(5) * strlen($text);
    $fontHeight = imagefontheight(5);
    $textX = $centerX - $fontWidth / 2;
    $textY = $centerY - $fontHeight / 2;
    
    imagestring($canvas, 5, $textX, $textY, $text, $white);
    
    return $canvas;
}

// Gerar todos os ícones
$successCount = 0;
$totalCount = count($sizes) + 1; // +1 para favicon

foreach ($sizes as $size) {
    echo "🔨 Gerando ícone {$size}x{$size}... ";
    
    $canvas = createIconFromSVG($size, $imagePath);
    $filename = "icon-{$size}x{$size}.png";
    $filepath = $outputDir . '/' . $filename;
    
    if (imagepng($canvas, $filepath)) {
        echo "✅ Salvo: $filename\n";
        $successCount++;
    } else {
        echo "❌ Erro ao salvar: $filename\n";
    }
    
    imagedestroy($canvas);
}

// Gerar favicon
echo "🔨 Gerando favicon (32x32)... ";
$canvas = createIconFromSVG(32, $imagePath);
if (imagepng($canvas, $outputDir . '/favicon.png')) {
    echo "✅ Salvo: favicon.png\n";
    $successCount++;
} else {
    echo "❌ Erro ao salvar: favicon.png\n";
}
imagedestroy($canvas);

echo "\n📊 Resumo:\n";
echo "✅ Ícones gerados com sucesso: $successCount/$totalCount\n";
echo "📱 Tamanhos para PWA: " . implode(', ', $sizes) . "\n";
echo "🌐 Favicon: favicon.png\n";

if ($successCount === $totalCount) {
    echo "\n🎉 Todos os ícones foram gerados com sucesso!\n";
    echo "📋 Arquivos criados:\n";
    echo "   - favicon.png (32x32)\n";
    foreach ($sizes as $size) {
        echo "   - icon-{$size}x{$size}.png\n";
    }
} else {
    echo "\n⚠️  Alguns ícones não puderam ser gerados.\n";
    echo "💡 Verifique se:\n";
    echo "   1. A extensão GD do PHP está habilitada\n";
    echo "   2. O arquivo SVG está válido\n";
    echo "   3. Permissões de escrita no diretório\n";
}

?>
