(() => {

const VOID_COLOR = 'black';
const WALL_COLOR = 'gold';
const HEIGHT_MIN = 10;
const WIDTH_MIN = 10;
const HEIGHT_MAX = 100;
const WIDTH_MAX = 100;
const RADIUS_MIN = 1;
const RADIUS_MAX = 10;

const { floor, min, max, PI } = Math;
const fitVal = (x, valMin, valMax) => max(valMin, min(x, valMax));
const numValue = elt => parseInt(elt.value);

class Grid {
    constructor(canvas, height, width, radius, content) {
        this.canvas = canvas;
        this.height = fitVal(height, HEIGHT_MIN, HEIGHT_MAX);
        this.width = fitVal(width, WIDTH_MIN, WIDTH_MAX);
        this.content = content || this.getNewContent();
        this.context = this.canvas.getContext('2d');
        this.isBeingPainted = false;
        this.paintbrush = false;
        this.setRadius(fitVal(radius, RADIUS_MIN, RADIUS_MAX), false);
        this.dim();
        this.canvas.addEventListener('mousedown', mouseEvent => {
            if(mouseEvent.buttons === 1) {
                const { line, column } = this.getPointedSquare(mouseEvent);
                this.isBeingPainted = true;
                this.paintbrush = !this.content[line][column];
                this.setSquare({ line, column });
            }
        });
        this.canvas.addEventListener('mousemove', mouseEvent => {
            if(this.isBeingPainted && mouseEvent.buttons === 1) {
                this.setSquare(this.getPointedSquare(mouseEvent));
            } else {
                this.isBeingPainted = false;
            }
        });
    }

    getNewContent() {
        return new Array(this.height)
            .fill(0)
            .map(() => new Array(this.width).fill(true));
    }

    setHeight(height) {
        if(height >= HEIGHT_MIN && height <= HEIGHT_MAX) {
            this.height = height;
            this.fitContent();
            this.dim();
        }
    }

    setWidth(width) {
        if(width >= WIDTH_MIN  && width <= WIDTH_MAX) {
            this.width = width;
            this.fitContent();
            this.dim();
        }
    }

    setContent(content) {
        if(content.length === this.height && content[0].length === this.width) {
            this.content = content;
            this.draw();
        }
    }

    setRadius(radius, draw=true) {
        if(radius >= RADIUS_MIN && radius <= RADIUS_MAX) {
            this.squareSide = radius * 2;
            this.radius = radius;
            draw && this.dim();
        }
    }

    setSquare({ line, column }) {
        this.content[line][column] = this.paintbrush;
        this.drawSquare(line, column, true);
    }


    getPointedSquare(mouseEvent) {
        const { left, top } = this.canvas.getBoundingClientRect();
        return {
            line: floor((mouseEvent.clientY - top)/this.squareSide),
            column : floor((mouseEvent.clientX - left)/this.squareSide)
        }
    }

    fitContent()
    {
        const exContent = this.content;
        const lineMax = min(exContent.length, this.height);
        const columnMax = min(exContent[0].length, this.width);
        this.content = this.getNewContent();
        for(let line = 0; line < lineMax; line++) {
            for(let column = 0; column < columnMax; column++) {
                this.content[line][column] = exContent[line][column];
            }
        }
    }

    dim() {
        this.canvas.height = `${this.height * this.squareSide}`;
        this.canvas.width = `${this.width * this.squareSide}`;
        this.draw();
    }

    draw() {
        for(let line = 0; line < this.height; line++) {
            for(let column = 0; column < this.width; column++) {
                this.drawSquare(line, column, false);
            }
        }
    }

    drawSquare(line, column, doAround) {
        [-1, 1].forEach(
            dl => [-1, 1].forEach(
                dc => this.drawCorner(line, column, dl, dc, doAround)
            )
        );
    }

    drawCorner(line, column, dl, dc, doAround) {
        const x = column * this.squareSide;
        const y = line * this.squareSide;
        const l = line + dl;
        const c = column + dc;
        const dlOk = l >= 0 && l < this.height;
        const dcOk = c >= 0 && c < this.width;
        const test = (mainCnt, dlCnt, dcCnt, dldcCnt) =>
                (mainCnt && !dlCnt && !dcCnt)
            ||  (!mainCnt && dlCnt && dcCnt && dldcCnt);
        if(dlOk && dcOk && test(
            this.content[line][column],
            this.content[l][column],
            this.content[line][c],
            this.content[l][c]
        )) {
            const [circleColor, bgColor] = this.content[line][column]
                ? [VOID_COLOR, WALL_COLOR]
                : [WALL_COLOR, VOID_COLOR];
            this.drawQuarterSquare(x, y, dl, dc, bgColor);
            this.drawQuarterCircle(x, y, dl, dc, circleColor);
        } else {
            const color = this.content[line][column] ? VOID_COLOR : WALL_COLOR;
            this.drawQuarterSquare(x, y, dl, dc, color);
        }
        if(doAround) {
            dlOk && this.drawCorner(l, column, -dl, dc, false);
            dcOk && this.drawCorner(line, c, dl, -dc, false);
            dlOk && dcOk && this.drawCorner(l, c, -dl, -dc, false);
        }
    }

    drawQuarterSquare(x, y, dl, dc, color) {
        const xRect = x + this.radius*(dc+1)/2;
        const yRect = y + this.radius*(dl+1)/2;
        this.context.fillStyle = color;
        this.context.fillRect(xRect, yRect, this.radius, this.radius);
    }

    drawQuarterCircle(x, y, dl, dc, color) {
        const startAngle = (3-dl*(2+dc)) * PI/4;
        const xCenter = x + this.radius;
        const yCenter = y + this.radius;
        this.context.fillStyle = color;
        this.context.beginPath();
        this.context.arc(xCenter, yCenter, this.radius, startAngle, startAngle + PI/2);
        this.context.lineTo(xCenter, yCenter);
        this.context.closePath();
        this.context.fill();
    }
}

const gridCanvas = document.getElementById('grid');
const heightInput = document.getElementById('heightGrid');
const widthInput = document.getElementById('widthGrid');
const grid = new Grid(gridCanvas, numValue(heightInput), numValue(widthInput), 5, null);

heightInput.min = `${HEIGHT_MIN}`;
heightInput.max = `${HEIGHT_MAX}`;
widthInput.min = `${WIDTH_MIN}`;
widthInput.max = `${WIDTH_MAX}`;

heightInput.addEventListener('change', ({ target }) => {
    grid.setHeight(numValue(target));
});

widthInput.addEventListener('change', ({ target }) => {
    grid.setWidth(numValue(target));
});

[{
    button: document.getElementById('up'),
    dl: -1,
    dc: 0
}, {
    button: document.getElementById('down'),
    dl: 1,
    dc: 0
}, {
    button: document.getElementById('left'),
    dl: 0,
    dc: -1
}, {
    button: document.getElementById('right'),
    dl: 0,
    dc: 1
}].forEach(({ button, dl, dc }) => button.addEventListener('click', () => {
    const { height, width, content } = grid;
    const newContent = grid.getNewContent();
    let l, c;
    for(let i = 0; i < height; i++) {
        for(let j = 0; j < width; j++) {
            l = i-dl;
            c = j-dc;
            if (l >= 0 && l < height && c >= 0 && c < width && !content[l][c])
                newContent[i][j] = false;
        }
    }
    grid.setContent(newContent);
}));

document.getElementById('zoomIn').addEventListener('click', () => grid.setRadius(grid.radius+1));
document.getElementById('zoomOut').addEventListener('click', () => grid.setRadius(grid.radius-1));

})();
