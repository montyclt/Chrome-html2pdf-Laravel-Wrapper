<?php

namespace MontyCLT\ChromePDF;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Spiritix\Html2Pdf\Output\StringOutput;
use Spiritix\Html2Pdf\Input\StringInput;
use Spiritix\Html2Pdf\Converter;
use Illuminate\Http\Response;

class PDF implements Responsable
{
    /**
     * @var \Illuminate\View\Factory
     */
    protected $viewFactory;

    /**
     * @var \Spiritix\Html2Pdf\Input\StringInput
     */
    protected $stringInput;

    /**
     * @var \Spiritix\Html2Pdf\Converter
     */
    protected $converter;

    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory;
     */
    protected $responseFactory;

    public function __construct(ViewFactory $viewFactory, ResponseFactory $responseFactory, StringInput $stringInput,
                                StringOutput $stringOutput)
    {
        $this->viewFactory = $viewFactory;
        $this->responseFactory = $responseFactory;
        $this->stringInput = $stringInput;
        $this->converter = new Converter($this->stringInput, $stringOutput);
    }

    /**
     * Use a view to generate the PDF content.
     *
     * @param  string $view
     * @param  array $data
     * @param  array $mergeData
     * @return PDF
     */
    public function loadView(string $view, array $data = [], array $mergeData = []): PDF
    {
        $this->stringInput->setHtml($this->viewFactory->make($view, $data, $mergeData)->render());

        return $this;
    }

    /**
     * Use raw HTML to generate the PDF content.
     *
     * @param  string $html
     * @return PDF
     */
    public function loadHtml(string $html): PDF
    {
        $this->stringInput->setHtml($html);

        return $this;
    }

    /**
     * Set an option.
     *
     * @see    https://github.com/spiritix/php-chrome-html2pdf#options
     * @param  string $key
     * @param  $value
     * @return PDF
     */
    public function setOption(string $key, $value): PDF
    {
        $this->converter->setOption($key, $value);

        return $this;
    }

    /**
     * Set multiple options.
     *
     * @see    https://github.com/spiritix/php-chrome-html2pdf#options
     * @param  array $options
     * @return PDF
     */
    public function setOptions(array $options): PDF
    {
        $this->converter->setOptions($options);

        return $this;
    }

    /**
     * Create a response that show the PDF in the browser.
     *
     * @param  string $filename
     * @param  int $status
     * @return Response
     */
    public function inline(string $filename = 'document.pdf', int $status = 200): Response
    {
        return $this->responseFactory->make($this->converter->convert()->get(), $status, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * Create a response that download the PDF.
     *
     * @param  string $filename
     * @param  int $status
     * @return Response
     */
    public function download(string $filename = 'document.pdf', int $status = 200): Response
    {
        return $this->responseFactory->make($this->converter->convert()->get(), $status, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        return $this->inline();
    }
}