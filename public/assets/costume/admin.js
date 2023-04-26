// Generate color RGB
function randColorRGB() {
    return (
        "rgba(" +
        Math.floor(Math.random() * 255) +
        "," +
        Math.floor(Math.random() * 255) +
        "," +
        Math.floor(Math.random() * 255) +
        ", 1)"
    );
}

// Generate color HEX
function randColorHex() {
    return "#" + Math.floor(Math.random() * 16777215).toString(16);
}
