# 🎨 Terminal Style Options

## Current Style: VS Code Terminal

You now have a **VS Code-inspired terminal** with:
- **Rounded corners** and modern shadows
- **Tab-style header** with terminal icon
- **Blue accent buttons** with hover effects
- **Professional VS Code color scheme**
- **Floating panel** with margins

## Alternative Style Options

### Option 1: 🌙 Dark Cyberpunk Theme
```css
/* Cyberpunk Terminal Style */
.terminal-cyberpunk {
    background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 100%);
    border: 2px solid #00ff41;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(0, 255, 65, 0.3), inset 0 0 20px rgba(0, 255, 65, 0.1);
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { box-shadow: 0 0 30px rgba(0, 255, 65, 0.3); }
    to { box-shadow: 0 0 40px rgba(0, 255, 65, 0.5); }
}

.terminal-cyberpunk .terminal-header {
    background: linear-gradient(90deg, #16213e 0%, #0f3460 100%);
    border-bottom: 2px solid #00ff41;
    color: #00ff41;
}

.terminal-cyberpunk .terminal-btn {
    background: linear-gradient(45deg, #00ff41, #00cc33);
    color: #000;
    border: none;
    text-shadow: 0 0 5px rgba(0, 255, 65, 0.8);
}
```

**JavaScript Theme:**
```javascript
theme: {
    background: '#0f0f23',
    foreground: '#00ff41',
    cursor: '#00ff41',
    selection: '#16213e',
    black: '#000000',
    red: '#ff0040',
    green: '#00ff41',
    yellow: '#ffff00',
    blue: '#0080ff',
    magenta: '#ff00ff',
    cyan: '#00ffff',
    white: '#ffffff'
}
```

### Option 2: 🌸 Soft Pink/Purple Theme
```css
/* Soft Pink Terminal Style */
.terminal-soft {
    background: linear-gradient(135deg, #2d1b69 0%, #11998e 100%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px rgba(45, 27, 105, 0.4);
}

.terminal-soft .terminal-header {
    background: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.terminal-soft .terminal-btn {
    background: linear-gradient(45deg, #ff6b9d, #c44569);
    color: white;
    border-radius: 20px;
    transition: all 0.3s ease;
}
```

### Option 3: 🔥 Retro Green Terminal
```css
/* Retro Green Terminal Style */
.terminal-retro {
    background: #001100;
    border: 3px solid #00ff00;
    border-radius: 0;
    font-family: 'Courier New', monospace;
    box-shadow: inset 0 0 50px rgba(0, 255, 0, 0.1);
}

.terminal-retro .terminal-header {
    background: #003300;
    border-bottom: 2px solid #00ff00;
    color: #00ff00;
    font-family: 'Courier New', monospace;
}

.terminal-retro .terminal-btn {
    background: #00ff00;
    color: #000000;
    border: 1px solid #00ff00;
    border-radius: 0;
    font-family: 'Courier New', monospace;
    font-weight: bold;
}
```

### Option 4: 🌊 Ocean Blue Theme
```css
/* Ocean Blue Terminal Style */
.terminal-ocean {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: 1px solid #4facfe;
    border-radius: 10px;
    box-shadow: 0 15px 35px rgba(31, 38, 135, 0.37);
}

.terminal-ocean .terminal-header {
    background: rgba(255, 255, 255, 0.15);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.terminal-ocean .terminal-btn {
    background: linear-gradient(45deg, #4facfe, #00f2fe);
    color: white;
    border-radius: 6px;
}
```

### Option 5: 🌟 Glassmorphism Theme
```css
/* Glassmorphism Terminal Style */
.terminal-glass {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    backdrop-filter: blur(20px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.terminal-glass .terminal-header {
    background: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.terminal-glass .terminal-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    backdrop-filter: blur(10px);
}
```

## How to Switch Styles

1. **Replace the CSS class** in the terminal panel:
   ```html
   <!-- Change from: -->
   <div id="terminal-panel" class="terminal-vscode">
   
   <!-- To any of: -->
   <div id="terminal-panel" class="terminal-cyberpunk">
   <div id="terminal-panel" class="terminal-soft">
   <div id="terminal-panel" class="terminal-retro">
   <div id="terminal-panel" class="terminal-ocean">
   <div id="terminal-panel" class="terminal-glass">
   ```

2. **Update the JavaScript theme** in `terminal.js`

3. **Add the corresponding CSS** to your style section

## Custom Animations

### Typing Effect
```css
@keyframes typing {
    from { width: 0; }
    to { width: 100%; }
}

.terminal-header {
    animation: typing 2s steps(40, end);
}
```

### Pulse Effect
```css
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.terminal-btn:hover {
    animation: pulse 0.5s ease-in-out;
}
```

## Which style would you like to try? 🎨
