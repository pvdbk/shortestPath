(() => {

const VOID_COLOR = 'black';
const WALL_COLOR = 'gold';
const NB_LINES_MIN = 10;
const NB_COLUMNS_MIN = 10;
const NB_LINES_MAX = 100;
const NB_COLUMNS_MAX = 100;
const RADIUS_MIN = 1;
const RADIUS_MAX = 10;

const { floor, min, max, PI } = Math;
const fitVal = (x, valMin, valMax) => max(valMin, min(x, valMax));
const numValue = elt => parseInt(elt.value);
const new2DArray = (nbLines, nbColumns, value) => new Array(nbLines)
    .fill(0)
    .map(() => new Array(nbColumns).fill(value));

class Grid {
    constructor(canvas, nbLines, nbColumns, radius, content) {
        this.canvas = canvas;
        this.nbLines = fitVal(nbLines, NB_LINES_MIN, NB_LINES_MAX);
        this.nbColumns = fitVal(nbColumns, NB_COLUMNS_MIN, NB_LINES_MAX);
        this.content = content || new2DArray(this.nbLines, this.nbColumns, true);
        this.context = this.canvas.getContext('2d');
        this.modifying = false;
        this.pen = false;
        this.setRadius(fitVal(radius, RADIUS_MIN, RADIUS_MAX), false);
        this.dim();
        this.canvas.addEventListener('mousedown', mouseEvent => {
            if(mouseEvent.buttons === 1) {
                const { line, column } = this.getPointedSquare(mouseEvent);
                this.modifying = true;
                this.pen = !this.content[line][column];
                this.setSquare({ line, column });
            }
        });
        this.canvas.addEventListener('mousemove', mouseEvent => {
            if(this.modifying && mouseEvent.buttons === 1)
                this.setSquare(this.getPointedSquare(mouseEvent));
            else
                this.modifying = false;
        });
    }

    setNbLines(nbLines) {
        if(nbLines >= NB_LINES_MIN && nbLines <= NB_LINES_MAX) {
            this.nbLines = nbLines;
            this.fitContent();
            this.dim();
        }
    }

    setNbColumns(nbColumns) {
        if(nbColumns >= NB_COLUMNS_MIN  && nbColumns <= NB_COLUMNS_MAX) {
            this.nbColumns = nbColumns;
            this.fitContent();
            this.dim();
        }
    }

    setContent(content) {
        if(content.length === this.nbLines && content[0].length === this.nbColumns)
        {
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
        this.content[line][column] = this.pen;
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
        const lineMax = min(exContent.length, this.nbLines);
        const columnMax = min(exContent[0].length, this.nbColumns);
        let line, column;
        this.content = new2DArray(this.nbLines, this.nbColumns, true);
        for(line = 0; line < lineMax; line++)
            for(column = 0; column < columnMax; column++)
                this.content[line][column] = exContent[line][column];
    }

    dim() {
        this.canvas.height = `${this.nbLines * this.squareSide}`;
        this.canvas.width = `${this.nbColumns * this.squareSide}`;
        this.draw();
    }

    draw() {
        let line, column;
        for(line = 0; line < this.nbLines; line++)
            for(column = 0; column < this.nbColumns; column++)
                this.drawSquare(line, column, false);
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
        const dlOk = l >= 0 && l < this.nbLines;
        const dcOk = c >= 0 && c < this.nbColumns;
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
        }
        else {
            const color = this.content[line][column] ? VOID_COLOR : WALL_COLOR;
            this.drawQuarterSquare(x, y, dl, dc, color);
        }
        if(doAround)
        {
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
const nbLinesInput = document.getElementById('nbLines');
const nbColumnsInput = document.getElementById('nbColumns');
const grid = new Grid(gridCanvas, numValue(nbLinesInput), numValue(nbColumnsInput), 5, null);

nbLinesInput.min = `${NB_LINES_MIN}`;
nbLinesInput.max = `${NB_LINES_MAX}`;
nbColumnsInput.min = `${NB_COLUMNS_MIN}`;
nbColumnsInput.max = `${NB_COLUMNS_MAX}`;

nbLinesInput.addEventListener('change', ({ target }) => {
    grid.setNbLines(numValue(target));
});

nbColumnsInput.addEventListener('change', ({ target }) => {
    grid.setNbColumns(numValue(target));
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
    const { nbLines, nbColumns, content } = grid;
    const newContent = new2DArray(grid.nbLines, grid.nbColumns, true);
    let i, j, l, c;
    for(i = 0; i < nbLines; i++)
        for(j = 0; j < nbColumns; j++) {
            l = i-dl;
            c = j-dc;
            if (l >= 0 && l < nbLines && c >= 0 && c < nbColumns && !content[l][c])
                newContent[i][j] = false;
        }
    grid.setContent(newContent);
}));

document.getElementById('zoomIn').addEventListener('click', () => grid.setRadius(grid.radius+1));
document.getElementById('zoomOut').addEventListener('click', () => grid.setRadius(grid.radius-1));

})();
